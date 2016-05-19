<?php

namespace Drupal\autotext_widget\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'autotext' widget.
 *
 * @FieldWidget(
 *   id = "autotext",
 *   label = @Translation("Autotext"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class AutotextWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'text' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['text'] = array(
      '#type' => 'textfield',
      '#title' => t('Text'),
      '#description'=> $this->t('Be careful with tokens, ensure that final text is not empty. For example, if this widget is used on a required field and the resulting text is empty, Drupal will fire "This value should not be null" error and there will be no way to fill the value in.'),
      '#default_value' => $this->getSetting('text'),
    );

    $entity_type_id = $this->fieldDefinition->getTargetEntityTypeId();
    $token_type = \Drupal::service('token.entity_mapper')
      ->getTokenTypeForEntityType($entity_type_id);
    $elements['text_tokens'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => [$token_type],
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Text: @text', ['@text' => $this->getSetting('text')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * Processes an entity setting values for the fields using Autotext widget.
   * 
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display
   */
  public static function processContentEntity(ContentEntityInterface $entity, EntityFormDisplayInterface $form_display) {
    foreach ($form_display->getComponents() as $field_name => $value) {
      $widget = $form_display->getRenderer($field_name);
      if ($widget instanceof self) {
        $token_type = \Drupal::service('token.entity_mapper')
          ->getTokenTypeForEntityType($entity->getEntityTypeId());
        $new_value = \Drupal::token()
          ->replace($widget->getSetting('text'), [$token_type => $entity], ['clear' => TRUE]);
        $entity->set($field_name, $new_value);
      }
    }
  }

}
