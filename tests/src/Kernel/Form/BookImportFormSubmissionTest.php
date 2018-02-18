<?php

namespace Drupal\Tests\form_validation\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\file\Entity\File;
use Drupal\form_validation\Form\BookImportForm;
use Drupal\KernelTests\KernelTestBase;

class BookImportFormSubmissionTest extends KernelTestBase {

  protected static $modules = [
    'system',
    'user',
    'file',
    'field',
    'node',
    'text',
    'taxonomy',
    'form_validation',
  ];

  public function setUp() {
    parent::setUp();

    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('node');
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('file');
    $this->installEntitySchema('user');
    $this->installConfig(['form_validation']);
  }

  public function testFormSubmission() {
    $book_form = new BookImportForm();
    $form = array();
    $form_state = new FormState();

    $csv = File::create([
      'uri' => __DIR__ . '/../../../fixtures/books.correct.csv',
    ]);
    $csv->save();

    $form_state->setValue('csv', [$csv->id()]);

    // Entity count queries.
    $taxonomy_count = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', 'book_authors')
      ->count();
    $node_count = \Drupal::entityQuery('node')
      ->condition('type', 'book')
      ->count();
    $this->assertEquals(0, $taxonomy_count->execute());
    $this->assertEquals(0, $node_count->execute());

    // Submit the form.
    $book_form->submitForm($form, $form_state);
    $this->assertEquals(3, $taxonomy_count->execute());
    $this->assertEquals(2, $node_count->execute());

    // Submit the form again.
    $book_form->submitForm($form, $form_state);
    $this->assertEquals(3, $taxonomy_count->execute());
    $this->assertEquals(4, $node_count->execute());

    // Submit and reset.
    $form_state->setValue('reset', 1);
    $book_form->submitForm($form, $form_state);
    $this->assertEquals(3, $taxonomy_count->execute());
    $this->assertEquals(2, $node_count->execute());
  }

}
