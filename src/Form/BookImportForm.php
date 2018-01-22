<?php

namespace Drupal\form_validation\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class BookImportForm extends FormBase {

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function getFormId() {
    return 'form_validation_import_books';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['csv'] = [
      // We want Drupal to GC the file after we're done. Which is why we go
      // for a managed file.
      '#type' => 'managed_file',
      '#title' => $this->t("List"),
      '#required' => TRUE,
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
        'form_validation_validate_csv' => [],
      ],
    ];

    $form['palindrome'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Palindrome"),
    ];

    if (\Drupal::currentUser()->hasPermission('administer book entities')) {
      $form['admin_field'] = [
        '#type' => 'checkbox',
        '#title' => $this->t("Check this if you're an admin"),
      ];
    }

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
    $palindrome = preg_replace('/[^a-z\d]/i', '', strtolower($form_state->getValue('palindrome')));
    if ($palindrome != strrev($palindrome)) {
      $form_state->setErrorByName('palindrome', $this->t("The palindrome value is incorrect."));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
