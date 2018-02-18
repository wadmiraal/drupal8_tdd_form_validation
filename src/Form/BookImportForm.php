<?php

namespace Drupal\form_validation\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

class BookImportForm extends FormBase {

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function getFormId() {
    return 'form_validation_book_import';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['csv'] = [
      '#type' => 'managed_file',
      '#title' => $this->t("Book list"),
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
        'form_validation_validate_csv' => [],
      ],
      '#required' => TRUE,
    ];

    if (\Drupal::currentUser()->hasPermission('administer books')) {
      $form['reset'] = [
        '#type' => 'checkbox',
        '#title' => $this->t("Reset all books"),
      ];
    }

    $form['palindrome'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Palindrome"),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t("Submit"),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $palindrome = $form_state->getValue('palindrome');
    if (!empty($palindrome)) {
      $palindrome = strtoupper(
        preg_replace('/[^\w]/', '', $palindrome)
      );
      if ($palindrome != strrev($palindrome)) {
        $form_state->setErrorByName('palindrome', $this->t("This is not a palindrome."));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('reset')) {
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $nids = \Drupal::entityQuery('node')
        ->condition('type', 'book')
        ->execute();
      $node_storage->delete(
        $node_storage->loadMultiple($nids)
      );
    }

    $file = File::load($form_state->getValue('csv')[0]);
    $rows = \Drupal::service('form_validation.csv_parser')->parseFile($file);

    // Get rid of the header.
    array_shift($rows);
    while ($row = array_shift($rows)) {
      $title = array_shift($row);
      $authors = array();
      while ($author = array_shift($row)) {
        $tids = \Drupal::entityQuery('taxonomy_term')
          ->condition('vid', 'book_authors')
          ->condition('name', $author)
          ->range(0, 1)
          ->execute();

        if (empty($tids)) {
          $term = Term::create([
            'vid' => 'book_authors',
            'name' => $author,
          ]);
          $term->save();
          $authors[] = $term->id();
        }
        else {
          $authors[] = reset($tids);
        }
      }
      $book = Node::create([
        'title' => $title,
        'type' => 'book',
      ]);
      $book->set('book_author', $authors);
      $book->save();
    }
  }

}
