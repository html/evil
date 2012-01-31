<?php
 /**
  * для работы необходимо установить jodconverter от openoffice, из Папки JodConverter скопировать в /etc/init.d openoffice.sh и запустить /etc/init.d/./openoffice.sh start
	-    во время работы jodconverter'а openoffice не будет работать
	-    остновить /etc/init.d/openoffice.sh stop

  * Отправляет комманду jodconverter'у для конвертирования 
    Конвертирует:
	Text Formats
   	    $inputFileName любой формат из
		OpenDocument Text (*.odt)
		OpenOffice.org 1.0 Text (*.sxw)
		Rich Text Format (*.rtf)
		Microsoft Word (*.doc)
		WordPerfect (*.wpd)
		Plain Text (*.txt)
		HTML1 (*.html) 	

	    $outputFileName любой формат из
		Portable Document Format (*.pdf)
		OpenDocument Text (*.odt)
		OpenOffice.org 1.0 Text (*.sxw)
		Rich Text Format (*.rtf)
		Microsoft Word (*.doc)
		Plain Text (*.txt)
		HTML2 (*.html)
		MediaWiki wikitext (*.wiki)

	Spreadsheet Formats
   	    $inputFileName  любой формат из
		OpenDocument Spreadsheet (*.ods)
		OpenOffice.org 1.0 Spreadsheet (*.sxc)
		Microsoft Excel (*.xls)
		Comma-Separated Values (*.csv)
		Tab-Separated Values (*.tsv) 

	    $outputFileName любой формат из
		Portable Document Format (*.pdf)
		OpenDocument Spreadsheet (*.ods)
		OpenOffice.org 1.0 Spreadsheet (*.sxc)
		Microsoft Excel (*.xls)
		Comma-Separated Values (*.csv)
		Tab-Separated Values (*.tsv)
		HTML2 (*.html)

	Presentation Formats
   	    $inputFileName  любой формат из
		OpenDocument Presentation (*.odp)
		OpenOffice.org 1.0 Presentation (*.sxi)
		Microsoft PowerPoint (*.ppt)

	    $outputFileName любой формат из
	 	Portable Document Format (*.pdf)
		Macromedia Flash (*.swf)
		OpenDocument Presentation (*.odp)
		OpenOffice.org 1.0 Presentation (*.sxi)
		Microsoft PowerPoint (*.ppt)
		HTML2 (*.html)

	Drawing Formats
   	    $inputFileName  любой формат из
		OpenDocument Drawing (*.odg)

	    $outputFileName любой формат из
	 	Scalable Vector Graphics (*.svg)
		Macromedia Flash (*.swf)   
  * @param  $inputFileName
  * @param  $outputFileName
  * @example
  *    if(!Evil_JodConverter::convert($inputFileName,$outputFileName))
  *        echo Evil_JodConverter::getError();
  *
  */
class Evil_JodConverter{
    protected static $_error='';

    public static function getError(){
	return self::$_error;
    }

    private static function _getExtension($fileName) {
        return substr(strrchr($fileName, '.'), 1);
    }

    public static function convert($inputFileName,$outputFileName){
	if(!file_exists($inputFileName)){
	    self::$_error.='file "'.$inputFileName.'" does not exist.'."\n";
	    return false;
	}
	$inputExt=self::_getExtension($inputFileName);
	$outputExt=self::_getExtension($outputFileName);
	$inputFormat='undef';
	$outputFormat='undef';
	$canConvert=false;
	switch($inputExt){
	    case 'odt':
	    case 'sxw':
	    case 'rtf':
	    case 'doc':
	    case 'wpd':
	    case 'txt':
	    case 'html':
		$inputFormat='text';
	    break;
	    case 'ods':
	    case 'sxc':
	    case 'xls':
	    case 'csv':
	    case 'tsv':
		$inputFormat='spreadsheet';
	    break;
	    case 'odp':
	    case 'sxi':
	    case 'ppt':
		$inputFormat='presentation';
	    break;
	    case 'odg':
		$inputFormat='drawing';
	    break;
	}
	if(in_array($inputFormat,array('text','spreadsheet','presentation'))&&in_array($outputExt,array('pdf','html')))
	    $canConvert=true;
	elseif(in_array($inputFormat,array('drawing','presentation'))&&$outputExt=='swf')
	    $canConvert=true;
	elseif($inputExt==$outputExt){
	    self::$_error.='conversion is not required.'."\n";
	    return false;
	}else{
	    switch($outputExt){
	        case 'odt':
   		case 'sxw':
	        case 'rtf':
	        case 'doc':
	        case 'txt':
 	        case 'wiki':
		    $outputFormat='text';
	        break;
	        case 'ods':
	        case 'sxc':
	        case 'xls':
	        case 'csv':
	        case 'tsv':
		    $outputFormat='spreadsheet';
	        break;
	        case 'odp':
	        case 'sxi':
	        case 'ppt':
		    $outputFormat='presentation';
	        break;
	        case 'odg':
		    $outputFormat='drawing';
 	        break;
	    }
	    $canConvert=($inputFormat==$outputFormat);
	}
        if($canConvert==true)
        {
           //return shell_exec('jodconverter '.$inputFileName.' '.$outputFileName);
            $cmd = 'java -jar '.__DIR__.'/JodConverter/jodconverter-cli-2.2.2.jar ' . $inputFileName . ' ' . $outputFileName;
            $ret = shell_exec($cmd);
            $ret = $cmd;
            return $ret;
        }
        else
        {
            self::$_error.='conversion from '.$inputFormat.' format ('.$inputExt.') '.$inputFormat.' format ('.$outputExt.') does not supported.'."\n";
            return false;
        }
    }
}
?>
