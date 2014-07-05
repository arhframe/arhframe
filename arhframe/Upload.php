<?php
import('arhframe.fct_recur');
/**
 *Upload one or many files
 *
 * @version $Id$
 * @copyright 2010
 */
class Upload
{
    public $max=1048576;
    private $chemin=NULL;
    private $extensionsValid;
    private $file;
    private $name=NULL;
    private $error = 3;
    /**
     * Constructor
     */
    public function __construct($file, $chemin=null, $newName=null)
    {
        if (!empty($file['name'])) {
            $this->file = $file;
        }
        $this->chemin = $chemin;
        $this->setNewName($newName);
    }
    public function setNewName($name)
    {
        if (is_array($this->file['name']) AND !empty($name)) {
            $this->name = makeArray($name);

        } elseif (!empty($name)) {
            $this->name = $name;
        }
    }
    public function setExtensionValid($extension)
    {
        $this->extensionsValid = makeArray($extension);
    }
    private function maxSize($fileSize)
    {
        if ($fileSize == null) {
            return;
        }
        if ($fileSize > $this->max) {
            throw new ArhframeExceptionFilesize("File size is too big: ". $fileSize ." have to be equals or less than ". $this->max);

        }
    }
    private function errorFile($fileError)
    {
        if ($fileError > 0) {
            throw new ArhframeException('Error occured during upload');
        }
    }
    private function extensionOk($fileName)
    {
        if (!empty($fileName)) {
            $extension = pathinfo($fileName);
            $extension = strtolower($extension['extension']);
            if (in_array($extension, $this->extensionsValid)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    private function moveFile($fileName, $fileTmpName, $name)
    {
        $extension = pathinfo($fileName);
        $extension = strtolower($extension['extension']);
        if (!empty($this->name)) {
            $fileName = $name .'.'. $extension;
        }
        $fileName = $this->chemin . $fileName;
        $newFile = move_uploaded_file($fileTmpName, $fileName);

        return $fileName;
    }
    public function upload()
    {
        if (is_array($this->file['name'])) {
            $file = array();
            for ($i=0; $i<=(count($this->file['name'])-1); $i++) {
                try {
                    $this->errorFile($this->file['error'][$i]);
                    $this->maxSize($this->file['size'][$i]);
                } catch (ExceptionFilesize $e) {
                    throw new ArhframeExceptionFilesize($e->getMessage());
                } catch (Exception $e) {
                    throw new ArhframeException($e->getMessage());
                }

                if ($this->extensionOk($this->file['name'][$i])) {
                    $file[] = $this->moveFile($this->file['name'][$i], $this->file['tmp_name'][$i], $this->name[$i]);
                    $this->error = null;
                } else {
                    throw new ArhframeExceptionExtension("Extension not valid");
                }
            }
        } else {
            try {
                $this->errorFile($this->file['error']);
                $this->maxSize($this->file['size']);
            } catch (ExceptionFilesize $e) {
                throw new ArhframeExceptionFilesize($e->getMessage());
            } catch (Exception $e) {
                throw new ArhframeException($e->getMessage());
            }

            if ($this->extensionOk($this->file['name'])) {
                $file = $this->moveFile($this->file['name'], $this->file['tmp_name'], $this->name);
                $this->error = null;
            } else {
                    throw new ArhframeExceptionExtension("Extension not valid");
            }
        }

        return $file;
    }
}
class ExceptionFilesize extends Exception
{

    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
/**
*
*/
class ExceptionExtension extends Exception
{

    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
