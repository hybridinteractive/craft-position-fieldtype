<?php
/**
 * Position Fieldtype plugin for Craft CMS 3.x.
 *
 * Brings back the Position fieldtype from Craft 2
 *
 * @link      https://rias.be
 *
 * @copyright Copyright (c) 2017 Rias
 */

namespace rias\positionfieldtype\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use phpDocumentor\Reflection\Types\This;
use rias\positionfieldtype\assetbundles\positionfieldtype\PositionFieldtypeAsset;
use yii\db\Schema;

/**
 * Position Field.
 *
 * Whenever someone creates a new field in Craft, they must specify what
 * type of field it is. The system comes with a handful of field types baked in,
 * and we’ve made it extremely easy for plugins to add new ones.
 *
 * https://craftcms.com/docs/plugins/field-types
 *
 * @author    Rias
 *
 * @since     1.0.0
 *
 * @property string      $contentColumnType
 * @property null|string $settingsHtml
 */
class Position extends Field
{
    // Public Properties
    // =========================================================================

    /**
     * Available options.
     *
     * @var string
     */
    public $options = [];

    /**
     * Default value.
     *
     * @var string
     */
    public $default = '';

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('position-fieldtype', 'Position');
    }

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules = array_merge($rules, [
            ['options', 'validateOptions'],
            ['default', 'string'],
        ]);

        return $rules;
    }

    /**
     * Since the options field takes an array with the position as the key name
     * and 0/1 as the value, we cannot use the normal "each" validator, but must
     * validate it ourselves.
     *
     * @param string $attribute attribute to validate
     */
    public function validateOptions($attribute)
    {
        $options = $this->$attribute;
        if (!is_array($options)) {
            $this->addError($attribute, Craft::t('position-fieldtype', '$options must be an array.'));
        }

        $allOptions = self::getOptions();
        foreach ($options as $key => $value) {
            if (!array_key_exists($key, $allOptions)) {
                $this->addError($attribute, Craft::t('position-fieldtype', 'Invalid key in $options'));
            }

            if ($value != 0 && $value != 1 && $value != '') {
                $this->addError($attribute, Craft::t('position-fieldtype', 'Invalid value for $options[{key}].', [
                    '{key}' => $key,
                ]));
            }
        }
    }

    /**
     * Returns the column type that this field should get within the content table.
     *
     * This method will only be called if [[hasContentColumn()]] returns true.
     *
     * @return string The column type. [[\yii\db\QueryBuilder::getColumnType()]] will be called
     *                to convert the give column type to the physical one. For example, `string` will be converted
     *                as `varchar(255)` and `string(100)` becomes `varchar(100)`. `not null` will automatically be
     *                appended as well.
     *
     * @see \yii\db\QueryBuilder::getColumnType()
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_STRING;
    }

    /**
     * Normalizes the field’s value for use.
     *
     * This method is called when the field’s value is first accessed from the element. For example, the first time
     * `entry.myFieldHandle` is called from a template, or right before [[getInputHtml()]] is called. Whatever
     * this method returns is what `entry.myFieldHandle` will likewise return, and what [[getInputHtml()]]’s and
     * [[serializeValue()]]’s $value arguments will be set to.
     *
     * @param mixed                 $value   The raw field value
     * @param ElementInterface|null $element The element the field is associated with, if there is one
     *
     * @return mixed The prepared field value
     */
    public function normalizeValue($value, ElementInterface $element = null): ?string
    {
        return $value;
    }

    /**
     * Returns the component’s settings HTML.
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     *
     * @return string|null
     */
    public function getSettingsHtml(): string
    {
        // Register our asset bundle
        Craft::$app->getView()->registerAssetBundle(PositionFieldtypeAsset::class);

        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId('position-fieldtype');
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        Craft::$app->getView()->registerJs("new PositionSelectInput('{$namespacedId}');");

        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'position-fieldtype/_components/fields/Position_settings',
            [
                'field'        => $this,
                'allOptions'   => array_keys(self::getOptions()),
                'id'           => $id,
                'namespacedId' => $namespacedId,
                'settings'     => $this->settings,
            ]
        );
    }

    /**
     * Returns the field’s input HTML.
     *
     * @param mixed                 $value   The field’s value.
     *                                       This will either be the [[normalizeValue() normalized value]],
     *                                       raw POST data (i.e. if there was a validation error), or null
     * @param ElementInterface|null $element The element the field is associated with, if there is one
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     *
     * @return string The input HTML.
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        // Register our asset bundle
        Craft::$app->getView()->registerAssetBundle(PositionFieldtypeAsset::class);

        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        Craft::$app->getView()->registerJs("new PositionSelectInput('{$namespacedId}');");

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'position-fieldtype/_components/fields/Position_input',
            [
                'name'         => $this->handle,
                'value'        => $value,
                'field'        => $this,
                'id'           => $id,
                'namespacedId' => $namespacedId,
                'allOptions'   => self::getOptions(),
            ]
        );
    }

    // Private Methods
    // =========================================================================

    /**
     * Returns the position options.
     *
     * @return array
     */
    private static function getOptions(): array
    {
        return [
            'left'       => Craft::t('position-fieldtype', 'Left'),
            'center'     => Craft::t('position-fieldtype', 'Center'),
            'right'      => Craft::t('position-fieldtype', 'Right'),
            'full'       => Craft::t('position-fieldtype', 'Full'),
            'drop-left'  => Craft::t('position-fieldtype', 'Drop-left'),
            'drop-right' => Craft::t('position-fieldtype', 'Drop-right'),
        ];
    }
}
