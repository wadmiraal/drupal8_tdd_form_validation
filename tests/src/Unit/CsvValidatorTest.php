<?php

namespace Drupal\form_validation\Tests\Unit;

use Drupal\file\FileInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\form_validation\CsvValidator;

class CsvValidatorTest extends UnitTestCase {
  use StringTranslationTrait;

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
    // same way. For this reason, we need a container, which is not yet setup.
    \Drupal::setContainer($this->getContainer());

    $base_path = realpath(__DIR__ . '/../../fixtures');
    $return = array();
    foreach ([
      'books.incorrect_data.csv' => [
        $this->t("Line @line doesn't specify an author.", ['@line' => 1]),
        $this->t("The book title on line @line is empty.", ['@line' => 2]),
      ],
      'books.incorrect_format.csv' => [
        $this->t("The CSV format is incorrect."),
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
