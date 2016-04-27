<?php
class Ftp_class {

	private $connectionId;
	private $loginOk = false;
	private $messageArray = array();

	public function __construct() {

	}

	private function logMessage($message) {
		$this->messageArray[] = $message;
	}

	public function getMessages() {
		return $this->messageArray;
	}

	public function sslConnect($server, $ftpUser, $ftpPassword) {

		$this->connectionId = ftp_ssl_connect($server);

		$loginResult = ftp_login($this->connectionId, $ftpUser, $ftpPassword);

		ftp_pwd($this->connectionId);
        
		if((!$this->connectionId) || (!$loginResult)) {
			$this->logMessage('ftp ssl connection has failed!');
			$this->logMessage('Attempted to connected to ' . $server . ' for user ' . $ftpUser, true);
			return false;
		} else {
			$this->logMessage('ftp ssl connection to ' . $server . ', for user ' . $ftpUser);
			$this->loginOk = true;
			return true;
		}
	}

	public function connect($server, $ftpUser, $ftpPassword, $isPassive = false) {

		// Set up a connection
		$this->connectionId = ftp_connect($server);

		// Login with gebruikersnaam en wachtwoord
		$loginResult = ftp_login($this->connectionId, $ftpUser, $ftpPassword);

		ftp_pasv($this->connectionId, $isPassive);

		// Check the connection
		if((!$this->connectionId) || (!$loginResult)) {
			$this->logMessage('FTP connection has failed!');
			$this->logMessage('Attempted to connected to ' . $server . ' for user ' . $ftpUser, true);
			return false;
		} else {
			$this->logMessage('Connected to ' . $server . ', for user ' . $ftpUser);
			$this->loginOk = true;
			return true;
		}
	}

	public function makeDir($directory) {

		if(ftp_mkdir($this->connectionId, $directory)) {
			$this->logMessage('Directory "' . $directory . '" created successfully');
			return true;
		} else {
			$this->logMessage('Failed creating directory "' . $directory . '"');
			return false;
		}
 	}

 	public function uploadFile($fileFrom, $fileTo) {
 		$asciiArray = array('txt', 'csv');
 		$extension = explode('.', $fileFrom);
 		$end = end($extension);

 		if(in_array($end, $asciiArray)) {
 			$mode = FTP_ASCII;
 		} else {
 			$mode = FTP_BINARY;
 		}

 		$upload = ftp_put($this->connectionId, $fileTo, $fileFrom, $mode);

 		if(!$upload) {

 			$this->logMessage('FTP upload has failed');
 			return false;
 		} else {

 			$this->logMessage('Uploaded "' . $fileFrom . '" as "' . $fileTo);
 			return true;
 		}
 	}

 	public function changeDir($directory) {
 		if(ftp_chdir($this->connectionId, $directory)) {
 			$this->logMessage('Current directory is now: ' . ftp_pwd($this->connectionId));
 			return true; 
 		} else {
 			$this->logMessage('Couldn\'t change directory');
 		    return false;
 		}
 	}

 	public function getDirListening($directory = '.') {

 		$contentsArray = ftp_nlist($this->connectionId, $directory);

 		return $contentsArray;
 	}

	public function downloadFile($fileFrom, $fileTo) {
		$asciiArray = array('txt', 'csv');
		$extension = explode('.', $fileFrom);
		$end = end($extension);

		if(in_array($end, $asciiArray)) {
			$mode = FTP_ASCII;
		} else {
			$mode = FTP_BINARY;
		}

		if(ftp_get($this->connectionId, $fileTo, $fileFrom, $mode, 0)) {
			$this->logMessage('file "' . $fileTo . '" successfully downloaded');
			return true;
		} else {
			$this->logMessage('There was an error downloading the file "' . $fileFrom . '" to "' . $fileTo . '"');
			return false;
		}
	}

    public function deleteFile($file) {

        if(ftp_delete($this->connectionId, $file)) {
            $this->logMessage('file "' . $file . '" successfully deleted');
            return true;
        } else {
            $this->logMessage('We could not delete "' . $file . '" something went wrong');
            return false;
        }
    }

    public function renameFile($oldFile, $newFile) {

        if(ftp_rename($this->connectionId, $oldFile, $newFile)) {
            $this->logMessage('Your file "' . $oldFile . '" is successfully renamed to "' . $newFile . '"');
            return true;
        } else {
            $this->logMessage('Your file "' . $oldFile . '" has not been renamed to "' . $newFile . '" something went wrong');
            return false;
        }
    }

    public function removeDirectory($directory) {

        if(ftp_rmdir($this->connectionId, $directory)) {
            $this->logMessage('Your directory "' . $directory . '"is successfully removed');
            return true;
        } else {
            $this->logMessage('Your directory "' . $directory . '"has not been removed something went wrong');
            return false;
        }
    }

    public function removeFullDirectory($directory) {
        ftp_chdir($this->connectionId, $directory);
        $files = ftp_nlist($this->connectionId, ".");

        if (file_exists($directory)) {
            if (empty($directory)) {
                $this->removeDirectory($directory);
            } else {
                foreach ($files as $file) {
                    ftp_delete($this->connectionId, $file);
                }
            }
        } else {
            $this->logMessage('Your directory "' . $directory . '" does not exists');
        }
    }

    public function lastFileChange($file) {

        $lastChange = ftp_mdtm($this->connectionId, $file);

        if($lastChange != -1) {
            $this->logMessage('"'. $file .'" was last modified on : ' . date("F d Y H:i:s.", $lastChange));
            return true;
        } else {
            $this->logMessage('Could not get the last modified time from the server...');
            return false;
        }
    }

    public function rawList($directory = '/') {

        $detailedList = ftp_rawlist($this->connectionId, $directory);

        return $detailedList;
    }

	public function __destruct() {
		if($this->connectionId) {
			ftp_close($this->connectionId);
		}
	}

}