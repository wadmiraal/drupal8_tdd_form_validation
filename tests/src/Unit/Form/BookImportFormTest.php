<?php

namespace Drupal\Tests\form_validation\Unit\Form;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Form\FormState;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\form_validation\Form\BookImportForm;
use Drupal\Tests\UnitTestCase;
use Drupal\user\Entity\User;

/**
 * @group form_validation_example
 */
class BookImportFormTest extends UnitTestCase {

  public function setUp() {
    parent::setUp();

    $container = new ContainerBuilder();

    $translations = $this->getMock(TranslationInterface::class);
    $container->set('string_translation', $translations);

    $user = $this->getMockBuilder(User::class)
      ->disableOriginalConstructor()
      ->getMock();
    $container->set('current_user', $user);

    \Drupal::setContainer($container);
  }

  public function testFormBuilding() {
    $import_form = new BookImportForm();
    $form = $import_form->buildForm(array(), new FormState());
    $this->assertArrayNotHasKey('reset', $form);

    // Enhance our mocked user.
    $user = \Drupal::currentUser();
    $user->expects($this->any())
      ->method('hasPermission')
      ->with($this->equalTo('administer books'))
      ->will($this->returnValue(TRUE));

    $form = $import_form->buildForm(array(), new FormState());
    $this->assertArrayHasKey('reset', $form);
  }

  /**
   * @dataProvider palindromeData
   */
  public function testPalindromeValidation($string, $passes) {
    $import_form = new BookImportForm();
    $form = array();
    $form_state = new FormState();

    $form_state->setValue('palindrome', $string);
    $import_form->validateForm($form, $form_state);
    if ($passes) {
      $this->assertCount(0, $form_state->getErrors());
    }
    else {
      $this->assertCount(1, $form_state->getErrors());
      $this->assertArrayHasKey('palindrome', $form_state->getErrors());
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
