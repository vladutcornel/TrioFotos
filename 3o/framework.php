<?php
/**
 * Helper file for loading TrioFramework classes
 * @package 3oFramework
 * @subpackage Core
 * @author Cornel Borină <cornel@scoalaweb.com>
 */
if (!defined("TRIO_DIR"))  
{  
    define("TRIO_DIR", __DIR__);  
}
// get the core classes
require_once TRIO_DIR . '/framework-core.php';

// register other framework classes
Whereis::register(array (
    // HTML namespace
    // generic elements
    'trio\html\Element' => TRIO_DIR.'/html/Element.php',
    'trio\html\HtmlElement' => TRIO_DIR.'/html/HtmlElement.php',
    // standard elements
    'trio\html\Block' => TRIO_DIR.'/html/elements/Block.php',
    'trio\html\Inline' => TRIO_DIR.'/html/elements/Inline.php',
    'trio\html\Table' => TRIO_DIR.'/html/elements/Table.php',
    'trio\html\Heading' => TRIO_DIR.'/html/elements/Heading.php',
    'trio\html\Image' => TRIO_DIR.'/html/elements/Image.php',
    'trio\html\HtmlList' => TRIO_DIR.'/html/elements/HtmlList.php',
    'trio\html\DefinitionList' => TRIO_DIR.'/html/elements/DefinitionList.php',
    'trio\html\DescriptionList' => TRIO_DIR.'/html/elements/DescriptionList.php',
    'trio\html\Link' => TRIO_DIR.'/html/elements/Link.php',
    'trio\html\Paragraph' => TRIO_DIR.'/html/elements/Paragraph.php',
    // form elements
    'trio\html\Button' => TRIO_DIR.'/html/forms/Button.php',
    'trio\html\CheckableFormElements' => TRIO_DIR.'/html/forms/CheckableFormElements.php',
    'trio\html\Checkbox' => TRIO_DIR.'/html/forms/Checkbox.php',
    'trio\html\CheckboxGroup' => TRIO_DIR.'/html/forms/CheckboxGroup.php',
    'trio\html\Form' => TRIO_DIR.'/html/forms/Form.php',
    'trio\html\FormElement' => TRIO_DIR.'/html/forms/FormElement.php',
    'trio\html\Label' => TRIO_DIR.'/html/forms/Label.php',
    'trio\html\Hidden' => TRIO_DIR.'/html/forms/Hidden.php',
    'trio\html\Dropdown' => TRIO_DIR.'/html/forms/Dropdown.php',
    'trio\html\Radio' => TRIO_DIR.'/html/forms/Radio.php',
    'trio\html\Input' => TRIO_DIR.'/html/forms/Input.php',
    'trio\html\Password' => TRIO_DIR.'/html/forms/Password.php',
    'trio\html\RadioGroup' => TRIO_DIR.'/html/forms/RadioGroup.php',
    'trio\html\Textarea' => TRIO_DIR.'/html/forms/Textarea.php',
    'trio\html\TextField' => TRIO_DIR.'/html/forms/TextField.php',
    'trio\html\ToggleField' => TRIO_DIR.'/html/forms/ToggleField.php',
    'trio\html\UploadForm' => TRIO_DIR.'/html/forms/UploadForm.php',
    
    // CSS namespace
    'trio\css\Style' => TRIO_DIR.'/css/Style.php',
    'trio\css\Unit' => TRIO_DIR.'/css/Unit.php',
    
    // Database
    'trio\db\Model' => TRIO_DIR.'/db/Model.php',
    'trio\db\Mysql' => TRIO_DIR.'/db/mysql/Mysql.php',
    
    // email
    'trio\mail\Mailer' => TRIO_DIR.'/utils/TrioMailer/class.phpmailer.php',
    'trio\mail\SMTP' => TRIO_DIR.'/utils/TrioMailer/class.smtp.php',
    
    // other utilities
    'trio\Timestamp' => TRIO_DIR.'/utils/Timestamp.php',
    
    // exceptions
    'UserInputException' => TRIO_DIR.'/exceptions/UserInputException.php',
));
