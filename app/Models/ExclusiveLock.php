<?php
namespace App\Models;
class ExclusiveLock
{
    protected $key   = null;  //user given value
    protected $file  = null;  //resource to lock
    protected $own   = FALSE; //have we locked resource
    protected $file_name = '';

    function __construct( $key ) 
    {
            $this->key = $key;
	    $this->file_name = "/tmp/ase_".$key.".lockfile";
    }


    function __destruct() 
    {
        if( $this->own == TRUE )
            $this->unlock( );
    }


    function lock( ) 
    {
	for($i=0;$i<=20;$i++) {
            $this->file = fopen($this->file_name, 'w+');
	    if(flock($this->file, LOCK_EX | LOCK_NB)) {
        	ftruncate($this->file, 0); // truncate file
		fwrite( $this->file, "Locked\n");
        	fflush( $this->file );
        	$this->own = TRUE;
        	return TRUE; // success
	    }
	    fclose($this->file);
	    sleep(1);
	}
        return FALSE; // success
    }


    function unlock( ) 
    {
    	if(!$this->own) 
    	   return FALSE;
	for($i=0;$i<=50;$i++) {
	    if(flock($this->file, LOCK_UN)) {
		fclose($this->file);
		unlink($this->file_name);
        	$this->own = FALSE;
        	return TRUE; // success
	    }
	    sleep(1);
	}
        return FALSE; // success
    }
};
