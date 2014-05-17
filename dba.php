<?php

/**
 * @description Conecction to databases
 * @version 2.5.2013-12-10@00:00
 * @author Angel Guadarrama
 */
class dbaClass {

    private $link;
    public $folio;
    public $extra;
    public $numRows;
    public $err;
    public $comment;
    public $status;
    public $debug;

    public function fnOpen() {
        $this->link = mysqli_connect('127.0.0.1', 'root', '', 'suspe');
        mysqli_set_charset($this->link, 'utf8');
    }

    public function fnQuery($sql) {
        $res = mysqli_query($this->link, $sql);
        $this->err = mysqli_error($this->link);
        return $res;
    }

    public function fnQueryAll($sql) {
        $result = mysqli_query($this->link, $sql);
        $this->numRows = mysqli_num_rows($result);
        return $result;
    }

    public function fnQueryExist($sql) {
        $this->comment = $sql;
        //$this->extra = $sql;
        //$sql = "SELECT * FROM estudios_lab_control WHERE folio = 378102";
        $result = mysqli_query($this->link, $sql);
        //mysqli_fetch_array($result);
        $this->numRows = mysqli_num_rows($result);
        $this->err = mysqli_error($this->link);
        $flag = ($this->numRows > 0) ? TRUE : FALSE;
        return $flag;
    }

    public function fnValidName($file) {
        $tFolio = str_replace('.pdf', '', $file);
        $this->folio = $tFolio;
        $flag = (is_numeric($tFolio)) ? TRUE : FALSE;
        return $flag;
    }

    public function fnCreatePersonalTbl($tblNew) {
        $this->status = 'err';
        $this->err = '';
        $this->debug = '';
        if (trim($tblNew) == ''):
            $this->err = '.Err.Nombre vacio';
        endif;
        if ($this->fnExistTable($tblNew)):
            $this->err = '.Err.Tabla ya existe';
        endif;
        if ($this->err === ''):
            /*
            $sqlC = "CREATE TABLE suspe." . $tblNew . "(
            personalId int(8) NOT NULL,
            credencial varchar(10) DEFAULT NULL,
            nombres varchar(65) DEFAULT NULL,
            apellidoPaterno varchar(55) DEFAULT NULL,
            apellidoMaterno varchar(55) DEFAULT NULL,
            sexo varchar(1) DEFAULT NULL,
            dtFechaNacimiento date DEFAULT NULL,
            tipo varchar(50) DEFAULT NULL,
            parentesco varchar(20) DEFAULT NULL,
            estatus int(1) DEFAULT NULL,
            dtVigencia date DEFAULT NULL,
            personalIdKey varchar(20) DEFAULT NULL,
            permisoActTemp int(1) NOT NULL DEFAULT 0,
            UNIQUE KEY personalId(personalId),
            KEY nombres(nombres),
            KEY apellidoPaterno(apellidoPaterno),
            KEY apellidoMaterno(apellidoMaterno)) 
            ENGINE  = InnoDB  DEFAULT CHARSET  = latin1";
             */
            $sqlC = "CREATE TABLE suspe." . $tblNew . "(
            personalId int(8) NOT NULL,
            credencial varchar(10) DEFAULT NULL,
            nombres varchar(65) DEFAULT NULL,
            apellidoPaterno varchar(55) DEFAULT NULL,
            apellidoMaterno varchar(55) DEFAULT NULL,
            sexo varchar(1) DEFAULT NULL,
            dtFechaNacimiento date DEFAULT NULL,
            tipo varchar(50) DEFAULT NULL,
            parentesco varchar(20) DEFAULT NULL,
            estatus int(1) DEFAULT NULL,
            dtVigencia date DEFAULT NULL,
            personalIdKey varchar(20) DEFAULT NULL,
            permisoActTemp int(1) NOT NULL DEFAULT 0
            ) 
            ENGINE  = InnoDB  DEFAULT CHARSET  = latin1";
            #$this->debug = substr($sqlC, 0, 249);
            $this->debug = $sqlC;
            $this->fnQuery($sqlC);
            $this->err = mysqli_error($this->link);
            $this->status = 'ready';
        endif;
    }

    public function fnExistTable($table) {
        $res = mysqli_query($this->link, "SELECT COUNT(*) AS count
            FROM information_schema.tables WHERE table_schema = 'suspe'
            AND table_name = '" . $table . "'");
        $row = mysqli_fetch_array($res);
        return $row[0] == 1;
    }

    public function fnRename($oldName, $newName) {
        $this->status = 'err';
        $this->err = '';
        $this->debug = '';
        $this->err .= (!$this->fnExistTable($oldName)) ? '.Err.Nombre tabla original no existe' : '';
        $this->err .= ($this->fnExistTable($newName)) ? '.Err.Nuevo nombre de tabla existe' : '';
        if ($this->err === '') {
            $sqlR = sprintf("RENAME TABLE suspe.%s TO suspe.%s", $oldName, $newName);
            $this->debug = $sqlR;
            $this->fnQuery($sqlR);
            $this->err = mysqli_error($this->link);
            $this->status = 'ready';
        }
    }

    public function fnInsertTbl($tblNew = '', $tblSource = '') {
        $this->status = 'err';
        $this->err = '';
        if ($tblNew == '' || $tblSource == '') {
            $this->err = '.Err.Falta un parametro o esta vacio';
        } else {
            if (!$this->fnExistTable($tblNew)):
                $this->err = '.Err.Tabla destino no existe';
            endif;
            if (!$this->fnExistTable($tblSource)):
                $this->err = '.Err.Tabla origen no existe';
            endif;
            if ($this->err === ''):
                //$sqlI = sprintf("INSERT INTO 'suspe'.'%s' SELECT * FROM 'suspe'.'%s'", $tblNew, $tblSource);
                $sqlI = sprintf("INSERT INTO suspe.%s SELECT * FROM suspe.%s LIMIT 0,10", $tblNew, $tblSource);
                $this->fnQuery($sqlI);
                $this->err = mysqli_error($this->link);
                $this->status = 'ready';
            endif;
        }
    }

    public function Drop($table = '') {
        $this->status = 'err';
        $this->err = '';
        $this->debug = '';
        if ($table == ''):
            $this->err = '.Err.Falta parametro o esta vacio';
        else:
            if (!$this->fnExistTable($table)):
                $this->err = '.Err.Tabla no existe';
            endif;
            if ($this->err === ''):
                $sql = sprintf("DROP TABLE %s", $table);
                $this->debug = $sql;
                $this->fnQuery($sql);
                $this->err = mysqli_error($this->link);
                $this->status = 'ready';
            endif;
        endif;
    }

    public function tablesUpdateDetail($table = 'N/D', $function = 'N/D', $user = 0) {
        $sql = sprintf("INSERT INTO tables_update_detail(btable,bsql,bfunction,berr,bstatus,user) VALUES('%s','%s','%s','%s','%s',%s)", $table, $this->debug, $function, $this->err, $this->status, $user);
        $this->fnQuery($sql);
        $this->err = mysqli_error($this->link);
    }

    public function connect() {
        // SP: CONEXION EN LOCALHOST
//        if($_SERVER['HTTP_HOST']=='localhost'){
//            $this->con = mysqli_connect('localhost', 'root', '','suspe');
//            mysqli_set_charset($this->con,'utf8');
//        }
        // SP: CONEXION CON EL URL 'PORTALSUSPE.COM'
//        if ($_SERVER['HTTP_HOST']=='portalsuspe.com') {
//            $this->con = mysql_connect('localhost', 'u227773_dba', '$u$p3');
//            mysql_select_db('u227773_suspe', $this->con);
//            mysql_set_charset('utf8',  $this->con);
//        }
        // SP: CONEXION CON EL URL 'WWW.PORTALSUSPE.COM'
//        if ($_SERVER['HTTP_HOST']=='www.portalsuspe.com') {
//            $this->con = mysql_connect('localhost', 'u227773_dba', '$u$p3');
//            mysql_select_db('u227773_suspe', $this->con);
//            mysql_set_charset('utf8',  $this->con);
//        }
    }

//    public function close(){
//        mysqli_close($this->con);
//    }
}

?>
