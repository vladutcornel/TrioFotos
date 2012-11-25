<?php  
/**  
 * Helper file to locate all the framework classes - Auto generated  
 * @package 3oScript  
 * @author Cornel Borina <cornel@scoalaweb.com>  
 */  

/**  
 * User defined classes  
 */  
global $WHEREIS_USER; 
if (!is_array($WHEREIS_USER)) 
    $WHEREIS_USER = array();  

/** 
 * TriO classes 
 */ 
$WHEREIS = array (
  'CSSAtribute' => '/css/CSSAtribute.php',
  'CSSColor' => '/css/CSSColor.php',
  'CSSGradient' => '/css/CSSGradient.php',
  'CSSUnit' => '/css/CSSUnit.php',
  'Style' => '/css/Style.php',
  'DBModel' => '/db/DBModel.php',
  'TMysql' => '/db/mysql/TMysql.php',
  'UserInputException' => '/exceptions/UserInputException.php',
  'GenerateWhereis' => '/GenerateWhereis.php',
  'Element' => '/html/Element.php',
  'HtmlBlock' => '/html/elements/HtmlBlock.php',
  'HtmlHeading' => '/html/elements/HtmlHeading.php',
  'HtmlImage' => '/html/elements/HtmlImage.php',
  'HtmlInline' => '/html/elements/HtmlInline.php',
  'HtmlList' => '/html/elements/HtmlList.php',
  'Link' => '/html/elements/Link.php',
  'Paragraph' => '/html/elements/Paragraph.php',
  'ScriptHead' => '/html/elements/ScriptHead.php',
  'Table' => '/html/elements/Table.php',
  'Button' => '/html/forms/Button.php',
  'CheckableFormElements' => '/html/forms/CheckableFormElements.php',
  'Checkbox' => '/html/forms/Checkbox.php',
  'CheckboxGroup' => '/html/forms/CheckboxGroup.php',
  'Form' => '/html/forms/Form.php',
  'FormElement' => '/html/forms/FormElement.php',
  'FormLabel' => '/html/forms/FormLabel.php',
  'Hidden' => '/html/forms/Hidden.php',
  'HtmlDropdown' => '/html/forms/HtmlDropdown.php',
  'HtmlRadio' => '/html/forms/HtmlRadio.php',
  'Input' => '/html/forms/Input.php',
  'PasswordField' => '/html/forms/PasswordField.php',
  'RadioGroup' => '/html/forms/RadioGroup.php',
  'Textarea' => '/html/forms/Textarea.php',
  'TextField' => '/html/forms/TextField.php',
  'ToggleField' => '/html/forms/ToggleField.php',
  'UploadForm' => '/html/forms/UploadForm.php',
  'HtmlElement' => '/html/HtmlElement.php',
  'TGlobal' => '/TGlobal.php',
  'TObject' => '/TObject.php',
  'TOCore' => '/TOCore.php',
  'Util' => '/Util.php',
  'TrioMail' => '/utils/TrioMail.php',
); 


/** 
 * For Trio Framework internal use only!!! 
 * Tries to load the class file for the given class.  
 * The user can add extra non-TriO classes by registering the class names and  
 * file paths to trio_whereis. 
 * @global array $WHEREIS 
 * @global array $WHEREIS_USER 
 * @param string $class_name 
 * @see trio_whereis 
 */ 
function trio_autoload($class_name){  
    global $WHEREIS;  
    global $WHEREIS_USER;  
      
    if (!defined("TRIO_DIR"))  
    {  
        define("TRIO_DIR", __DIR__);  
    }  
    // try to load TriO script class  
    if (isset($WHEREIS[$class_name]))  
    {  
        include TRIO_DIR.'/'.$WHEREIS[$class_name];  
    }  
      
    // try to load User-defined class  
    if (isset($WHEREIS_USER[$class_name]))  
    {  
        include $WHEREIS_USER[$class_name];  
    }  
}  

/*  
 * Register autoload function and set it to prepand (3rd param) so other autoload functions can be declared  
 */  
spl_autoload_register ('trio_autoload', true, true);  

/**  
 * Tell the script where to look for the invoked class  
 * You can provide a parameters list with the odd index (1st param, 3rd...)   
 * being the class names andd the even parameters being the file path.  
 * Or you can directly provide an associative array with the keys being the   
 * class names and the values the path  
 * @param array $whereis array(class_name=>file_path)  
 */  
function trio_whereis()  
{  
    $first_arg = func_get_arg(0);  
    if (!is_array($first_arg))  
    {  
        // we got a list  
        $nr_args = func_num_args();  
        $args = func_get_args();  
        $new_args = array();  
        for ($i = 1; $i < $nr_args; $i+=2)  
        {  
            $new_args[$args[$i-1]] = $args[$i];  
        }  
        trio_whereis($new_args);  
        return;  
    }  
    global $WHEREIS_USER;  
    foreach($first_arg as $class=>$file)  
    {  
        $WHEREIS_USER[$class] = $file;  
    }  
}