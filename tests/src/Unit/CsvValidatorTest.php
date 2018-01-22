<?php

namespace Drupal\form_validation\Tests\Unit\Form;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\file\FileInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\form_validation\CsvValidator;

class CsvValidatorTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    \Drupal::setContainer($this->getContainer());
  }

  /**
   * @dataProvider csvDataProvider
   */
  public function testCsvValidation($file, $expected) {
    $validator = new CsvValidator();
    $this->assertEquals($expected, $validator->validate($file));
  }

  /**
   * Data provider for testing the CSV validation.
   */
  public function csvDataProvider() {
    // Because our validator uses t(), and Drupal 8 doesn't return a string when
    // calling t(), but an object, we need to format our expected outputs in the
    // same way. For this reason, we use an instance of our class, and pass the
    // expectations through t().
    \Drupal::setContainer($this->getContainer());
    $validator = new CsvValidator();

    $base_path = realpath(__DIR__ . '/../../fixtures');
    $return = array();
    foreach ([
      'books.incorrect_data.csv' => [
        $validator->t("Line @line doesn't specify an author.", ['@line' => 1]),
        $validator->t("The book title on line @line is empty.", ['@line' => 2]),
      ],
      'books.incorrect_format.csv' => [
        $validator->t("The CSV format is incorrect.")
      ],
      'books.correct.csv' => [],
    ] as $file_name => $expected) {
      $file = $this->getMock(FileInterface::class);
      $file->expects($this->any())
        ->method('getFileUri')
        ->will($this->returnValue("$base_path/$file_name"));
      $return[$file_name] = [$file, $expected];
    }
    return $return;
  }

  protected function getContainer() {
    $container = new ContainerBuilder();
    $translations = $this->getMock(TranslationInterface::class);
    $container->set('string_translation', $translations);
    return $container;
  }
}
