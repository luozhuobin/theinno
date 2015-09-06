<?PHP
/**
 * 
 * @author trouble
 *@property mysql $db
 *@property Session $Session
 */
Class Model_About extends Init
{

	function __construct($view=false)
	{
		parent::__construct();
	}

    public function init()
    {
		$this->tpl->display('about');
    }
    
}
?>
