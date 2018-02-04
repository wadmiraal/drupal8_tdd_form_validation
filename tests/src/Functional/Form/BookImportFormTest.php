<?php

namespace Drupal\Tests\form_validation\Functional\Form;

use Drupal\Tests\BrowserTestBase;

/**
 * @group form_validation_functional
 */
class BookImportFormTest extends BrowserTestBase {

  public static $modules = ['file', 'form_validation'];

  public function testFileUploadValidation() {
    $this->drupalLogin($this->drupalCreateUser(['import books']));

    $edit = [
      'files[csv]' => drupal_realpath(__DIR__ . '/../../../fixtures/books.incorrect_format.csv'),
    ];
    $this->drupalPostForm('book/import', $edit, t("Submit"));
    $this->assertSession()->pageTextContains("The CSV format is incorrect. Use commas.");

    $edit = [
      'files[csv]' => drupal_realpath(__DIR__ . '/../../../fixtures/books.incorrect_data.csv'),
    ];
    $this->drupalPostForm('book/import', $edit, t("Submit"));
    $this->assertSession()->pageTextContains("The author on line 1 is empty. You must provide at least one author.");
    $this->assertSession()->pageTextContains("The book title on line 2 is empty. You must provide a title for each book.");

    $edit = [
      'files[csv]' => drupal_realpath(__DIR__ . '/../../../fixtures/books.correct.csv'),
    ];
    $this->drupalPostForm('book/import', $edit, t("Submit"));
    $this->assertSession()->pageTextNotMatches("/The author on line \d is empty/");
    $this->assertSession()->pageTextNotMatches("/The book title on line 2 is empty/");
  }

  public function testFormBuilding() {
    $this->drupalLogin($this->drupalCreateUser(['import books']));
    $this->drupalGet('book/import');
    $this->assertSession()->fieldNotExists('reset');

    $this->drupalLogin($this->drupalCreateUser(['import books', 'administer books']));
    $this->drupalGet('book/import');
    $this->assertSession()->fieldExists('reset');
  }

  /**
   * @dataProvider palindromeData
   */
  public function testPalindromeValidation($string, $passes) {
    $this->drupalLogin($this->drupalCreateUser(['import books']));

    $edit = [
      'palindrome' => $string,
    ];
    $this->drupalPostForm('book/import', $edit, t("Submit"));
    if ($passes) {
      $this->assertSession()->pageTextNotContains("This is not a palindrome");
    }
    else {
      $this->assertSession()->pageTextContains("This is not a palindrome");
    }
  }

  public function palindromeData() {
    return [
      [NULL, TRUE],
      ['', TRUE],
      ['A', TRUE],
      ['Was it a car or a cat I saw?', TRUE],
      ['A man, a plan, a canal, Panama!', TRUE],
      ['ABCDE', FALSE],
    ];
  }

}
