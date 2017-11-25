<?php
/**
 * Position Fieldtype plugin for Craft CMS 3.x
 *
 * Brings back the Position fieldtype from Craft 2
 *
 * @link      https://rias.be
 * @copyright Copyright (c) 2017 Rias
 */

namespace rias\positionfieldtype\fields;

use rias\positionfieldtype\assetbundles\PositionFieldtype\PositionFieldtypeAsset;
use rias\positionfieldtype\PositionFieldtype;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use yii\db\Schema;
use craft\helpers\Json;

/**
 * Position Field
 *
 * Whenever someone creates a new field in Craft, they must specify what
 * type of field it is. The system comes with a handful of field types baked in,
 * and we’ve made it extremely easy for plugins to add new ones.
 *
 * https://craftcms.com/docs/plugins/field-types
 *
 * @author    Rias
 * @package   PositionFieldtype
 * @since     1.0.0
 */
class Position extends Field
{
    // Public Properties
    // =========================================================================

    /**
     * Some attribute
     *
     * @var string
     */
    public $options = [];

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
    public function rules()
    {
        $rules = parent::rules();
        $rules = array_merge($rules, [
            ['options', 'each', 'rule' => ['string']],
        ]);
        return $rules;
    }

    /**
     * Returns the column type that this field should get within the content table.
     *
     * This method will only be called if [[hasContentColumn()]] returns true.
     *
     * @return string The column type. [[\yii\db\QueryBuilder::getColumnType()]] will be called
     * to convert the give column type to the physical one. For example, `string` will be converted
     * as `varchar(255)` and `string(100)` becomes `varchar(100)`. `not null` will automatically be
     * appended as well.
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
    public function normalizeValue($value, ElementInterface $element = null)
    {
        return $value;
    }

    /**
     * Returns the component’s settings HTML.
     *
     * @return string|null
     */
    public function getSettingsHtml()
    {
        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'position-fieldtype/_components/fields/Position_settings',
            [
                'field' => $this,
                'allOptions' => array_keys(static::getOptions()),
                'settings'   => $this->settings,
            ]
        );
    }

    /**
     * Returns the field’s input HTML.
     *
     * @param mixed $value                           The field’s value.
     *                                               This will either be the [[normalizeValue() normalized value]],
     *                                               raw POST data (i.e. if there was a validation error), or null
     * @param ElementInterface|null $element         The element the field is associated with, if there is one
     *
     * @return string The input HTML.
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        // Register our asset bundle
        Craft::$app->getView()->registerAssetBundle(PositionFieldTypeAsset::class);

        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        Craft::$app->getView()->registerJs("new PositionSelectInput('{$namespacedId}');");

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'position-fieldtype/_components/fields/Position_input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
                'allOptions' => self::getOptions()
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
    private static function getOptions()
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
