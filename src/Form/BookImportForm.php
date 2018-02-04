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
    ];

    if (\Drupal::currentUser()->hasPermission('administer books')) {
      $form['reset'] = [
        '#type' => 'checkbox',
        '#title' => $this->t("Reset all books"),
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
    parent::validateForm($form, $form_state); // TODO: Change the autogenerated stub
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }

}
