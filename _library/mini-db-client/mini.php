<?php 

    error_reporting(0);

    // configuration starts
    // initialize return variable
    $mini_db_client_returnData = null;
    
    
    // Database configuration
    $mini_db_client_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DB);
    
    
    // echoing error on connection error
    if($mini_db_client_connection->connect_errno){
        retResponse(500, "Failed to connect to MySQL: ".$mini_db_client_connection->connect_error);
        exit;
    }
    // configuration Complete
    

                    
    // If u r using lower version than PHP8 simply uncomment below functions

    function mini_str_starts_with($str, $substr){
        for($i=0;$i<strlen($substr);$i++){
            if($str[$i] !== $substr[$i]) return(false);
        }
        return(true);
    }

    function mini_str_ends_with($str, $substr){
        return(mini_str_starts_with(strrev($str), strrev($substr)));
    }

    function mini_str_contains($str, $substr){
        if(strpos($str, $substr) === false) return(false);
        return(true);
    }


    
    class MINI{

        public function __construct(){ 
            // Constructor
        }

        
        function runTimeError($mName){
            $GLOBALS['mini_db_client_returnData'] = "Run Time Error Occured on initializing function: $mName";
        }

        // For getting single value from database
        // field is for single field
        // from is for table name
        // whereCond is for where Condition
        // limit is for limit i.e. (0,5) is similar to (limit 0,5), * is similar to all, default is 1
        public static function getSValue($field, $from, $whereCond = 1, $limit = 1){
            try{
                (new self)->runTimeError("getSValue");
                if($field == '*'){
                    throw new Exception("You can use getMValue() instead of getSValue() for (*)");
                }
                $one = 0;
                switch ($limit) {
                    case 1:
                        $one = 1;
                        $limit = null;
                        break;
                    case '*':
                        $limit = null;
                        break;
                    default:
                        $limit = "limit $limit";
                        break;
                }
                $qry = "select $field from $from where $whereCond $limit";
                $selector = $GLOBALS['mini_db_client_connection']->query($qry);
                if(!$selector){
                    throw new Exception("Query:- $qry <br/>MySQL Error:- ".$GLOBALS['mini_db_client_connection']->error);
                }
                $selector = mysqli_fetch_all($selector, MYSQLI_ASSOC);
                if(count($selector) == 0){
                    $GLOBALS['mini_db_client_returnData'] = null;
                }
                else{
                    $GLOBALS['mini_db_client_returnData'] = null;
                    if($one == 1){
                        $GLOBALS['mini_db_client_returnData'] = $selector[0][$field];
                    }
                    else{
                        $GLOBALS['mini_db_client_returnData'] = $selector;
                    }
                }
            }
            catch(Exception $e){
                retResponse(500, 'Something went wrong..!', [
                    "debug" => $e->getMessage()
                ]);
                $GLOBALS['mini_db_client_returnData'] = $e->getMessage();
            }
            finally{
                return $GLOBALS['mini_db_client_returnData'];
            }            
        }


        // For getting multiple value from database
        // field is array for fields | single value for *
        // from is for table name
        // whereCond is for where Condition
        // limit is for limit i.e. (0,5) is similar to (limit 0,5), * is similar to all, default is 1
        // remove is for removing some parameters while fetching all data
        // substr at last will checks that substr at condition occurs or not
        // substr at first will checks that substr at condition occurs or not
        // substr at (any word except last,first) will checks that substr at condition occurs or not
        // must in those condition while substr at (key-word) is set and you have to fetch something important field from that condition
        public static function getMValue($field, $from, $whereCond = 1, $limit = 1, $remove = [], $must = []){
            try{
                (new self)->runTimeError("getMValue");
                if(is_array($field)){
                    $field = implode(', ', $field);
                }
                switch ($limit) {
                    case 1:
                        $limit = "limit 0,1";
                        break;
                    case '*':
                        $limit = null;
                        break;
                    default:
                        $limit = "limit $limit";
                        break;
                }
                $qry = "select $field from $from where $whereCond $limit";
                $selector = $GLOBALS['mini_db_client_connection']->query($qry);
                if(!$selector){
                    throw new Exception("Query:- $qry <br/>MySQL Error:- ".$GLOBALS['mini_db_client_connection']->error);
                }
                $selector = mysqli_fetch_all($selector, MYSQLI_ASSOC);
                if(count($selector) == 0){
                    $GLOBALS['mini_db_client_returnData'] = null;
                }
                else{
                    $GLOBALS['mini_db_client_returnData'] = null;
                    $GLOBALS['mini_db_client_returnData'] = $selector;
                    if(! is_array($remove)){
                        $remove = explode(',', $remove);
                    }
                    if(! is_array($must)){
                        $must = explode(',',$must);
                    }
                    $remove = array_map('trim',array_filter($remove));
                    $must = array_map('trim',array_filter($must));


                    foreach($remove as $keyRemove => $valueRemove){
                        if(mini_str_contains($valueRemove, ' at ')){
                            $valueAsCommand = explode(' at ',$valueRemove);
                            switch ($valueAsCommand[1]) {
                                case 'last':
                                    $operationMethod = 'mini_str_ends_with';
                                    break;
                                case 'first':
                                    $operationMethod = 'mini_str_starts_with';
                                    break;
                                default:
                                    $operationMethod = 'mini_str_contains';
                                    break;
                            }
                            foreach ($GLOBALS['mini_db_client_returnData'] as $keyReturn => $valueReturn) {
                                foreach ($valueReturn as $keyCommand => $valueCommand) {
                                    if($operationMethod($keyCommand,$valueAsCommand[0])){
                                        if(! in_array($keyCommand, $must)){
                                            unset($GLOBALS['mini_db_client_returnData'][$keyReturn][$keyCommand]);
                                        }
                                    }
                                }
                            }
                        }
                        else{
                            foreach ($GLOBALS['mini_db_client_returnData'] as $keyReturn => $valueReturn) {
                                unset($GLOBALS['mini_db_client_returnData'][$keyReturn][$valueRemove]);
                            }
                        }
                    }
                }
            }
            catch(Exception $e){
                $GLOBALS['mini_db_client_returnData'] = $e->getMessage();
            }
            finally{
                return $GLOBALS['mini_db_client_returnData'];
            } 
        }


        // $keyValueSet indicates set of keys and values...
        /*
            $keyValueSet = array(
                "field"=>array("name","discription","email","datetime"),
                "value"=>array(
                    array(
                        "Test",
                        "Testing insertion method",
                        "test@test.69hub",
                        "2021-04-12 16:58:33"
                    ),
                    array(
                        "Test2",
                        "Testing insertion method2",
                        "test2@test.69hub",
                        "2021-04-12 16:58:34"
                    )
                )
            );
        */
        public static function insert($table, $keyValueSet){
            try{
                (new self)->runTimeError("insert");
                $keySet = $valueSet = array();
                if( (!isset($keyValueSet['field'])) || (!isset($keyValueSet['value'])) || (!is_array($keyValueSet['field'])) || (!is_array($keyValueSet['value'])) ) throw new Exception("Check Your keyValueSet array..!");
                foreach ($keyValueSet['field'] as $key => $value) {
                    $keySet[] = "`".$value."`";
                }
                foreach ($keyValueSet['value'] as $key => $value) {
                    $tempValueSet = array();
                    if(!is_array($value)) throw new Exception("Check Your keyValueSet array..!");
                    foreach ($value as $keyValue => $valueValue) {
                        $tempValueSet[] = "'".$valueValue."'";
                    }
                    $valueSet[] = "(".implode(',',$tempValueSet).")";
                }
                $keySet = implode(', ',$keySet);
                $valueSet = implode(', ',$valueSet);
                $qry = "insert into $table($keySet) values $valueSet";
                $insertion = $GLOBALS['mini_db_client_connection']->query($qry);
                if(!$insertion){
                    throw new Exception("Query:- $qry <br/>MySQL Error:- ".$GLOBALS['mini_db_client_connection']->error);
                }
                $GLOBALS['mini_db_client_returnData'] = $GLOBALS['mini_db_client_connection']->insert_id;
            }
            catch(Exception $e){
                retResponse(500, 'Something went wrong..!', [
                    "debug" => $e->getMessage()
                ]);
                $GLOBALS['mini_db_client_returnData'] = $e->getMessage();
            }
            finally{
                return $GLOBALS['mini_db_client_returnData'];
            } 
        }




        // $keyValueSet indicates set of keys and values of a directly form or formate as defined
        /*
            $keyValueSet = array(
                "name"=>"Test",
                "discription"=>"Testing insertion method",
                "email"=>"test@test.69hub",
                "datetime"=>"2021-04-12 16:58:33",
                "submit"=>"Click"
            );
        */
        // $except indicates array or coma (,) saperated string of fields which are not being consider while insertion
        public static function insertForm($table, $keyValueSet, $except = []){
            try{
                (new self)->runTimeError("insertForm");
                if(!is_array($except)) {
                    $except = explode(",",$except);
                }
                foreach ($except as $key => $value) {
                    $except[$key] = trim($value);
                }
                $keySet = $valueSet = array();
                foreach ($keyValueSet as $key => $value) {
                    if(in_array($key, $except)) continue;
                    $keySet[] = "`".$key."`";
                    $valueSet[] = "'".$value."'";
                }
                $keySet = implode(', ',$keySet);
                $valueSet = implode(', ',$valueSet);
                $qry = "insert into $table($keySet) values ($valueSet)";
                $insertion = $GLOBALS['mini_db_client_connection']->query($qry);
                if(!$insertion){
                    throw new Exception("Query:- $qry <br/>MySQL Error:- ".$GLOBALS['mini_db_client_connection']->error);
                }
                $GLOBALS['mini_db_client_returnData'] = $GLOBALS['mini_db_client_connection']->insert_id;
            }
            catch(Exception $e){
                retResponse(500, 'Something went wrong..!', [
                    "debug" => $e->getMessage()
                ]);
                $GLOBALS['mini_db_client_returnData'] = $e->getMessage();
            }
            finally{
                return $GLOBALS['mini_db_client_returnData'];
            } 
        }





        // $keyValueSet indicates set of keys and values of a directly form or formate as defined
        /*
            $keyValueSet = array(
                "name"=>"Test",
                "discription"=>"Testing insertion method",
                "email"=>"test@test.69hub",
                "datetime"=>"2021-04-12 16:58:33",
                "submit"=>"Click"
            );
        */
        // $except indicates array or coma (,) saperated string of fields which are not being consider while insertion
        // $whereCond indicates condition after where keyword
        public static function update($table, $keyValueSet, $whereCond, $except = []){
            try{
                (new self)->runTimeError("update");
                if(!is_array($except)) {
                    $except = explode(",",$except);
                }
                foreach ($except as $key => $value) {
                    $except[$key] = trim($value);
                }
                $keyValueUpdate = array();
                foreach ($keyValueSet as $key => $value) {
                    if(in_array($key, $except)) continue;
                    $keyValueUpdate[] = "`".$key."`"." = "."'".$value."'";
                }
                $keyValueUpdate = implode(', ',$keyValueUpdate);
                $qry = "update $table set $keyValueUpdate where $whereCond";
                $updation = $GLOBALS['mini_db_client_connection']->query($qry);
                if(!$updation){
                    throw new Exception("Query:- $qry <br/>MySQL Error:- ".$GLOBALS['mini_db_client_connection']->error);
                }
                $GLOBALS['mini_db_client_returnData'] = $updation;
            }
            catch(Exception $e){
                retResponse(500, 'Something went wrong..!', [
                    "debug" => $e->getMessage()
                ]);
                $GLOBALS['mini_db_client_returnData'] = $e->getMessage();
            }
            finally{
                return $GLOBALS['mini_db_client_returnData'];
            } 
        }




        // $whereCond indicates condition after where keyword
        public static function delete($table, $whereCond){
            try{
                (new self)->runTimeError("delete");
                $qry = "delete from $table where $whereCond";
                $deletion = $GLOBALS['mini_db_client_connection']->query($qry);
                if(!$deletion){
                    throw new Exception("Query:- $qry <br/>MySQL Error:- ".$GLOBALS['mini_db_client_connection']->error);
                }
                $GLOBALS['mini_db_client_returnData'] = $deletion;
            }
            catch(Exception $e){
                retResponse(500, 'Something went wrong..!', [
                    "debug" => $e->getMessage()
                ]);
                $GLOBALS['mini_db_client_returnData'] = $e->getMessage();
            }
            finally{
                return $GLOBALS['mini_db_client_returnData'];
            } 
        }



        
        // $whereCond indicates condition after where keyword
        public static function query($query){
            try{
                (new self)->runTimeError("query");
                $qry = $query;
                $outputQuery = $GLOBALS['mini_db_client_connection']->query($qry);
                if(!$outputQuery){
                    throw new Exception("Query:- $qry <br/>MySQL Error:- ".$GLOBALS['mini_db_client_connection']->error);
                }
                $GLOBALS['mini_db_client_returnData'] = $outputQuery;
            }
            catch(Exception $e){
                $GLOBALS['mini_db_client_returnData'] = $e->getMessage();
            }
            finally{
                return $GLOBALS['mini_db_client_returnData'];
            } 
        }



        public static function describe($table){
            try{
                (new self)->runTimeError("describe");
                $qry = "desc $table";
                $selector = $GLOBALS['mini_db_client_connection']->query($qry);
                if(!$selector){
                    throw new Exception("Query:- $qry <br/>MySQL Error:- ".$GLOBALS['mini_db_client_connection']->error);
                }
                $selector = mysqli_fetch_all($selector, MYSQLI_ASSOC);
                if(count($selector) == 0){
                    throw new Exception("No data found");
                }
                $GLOBALS['mini_db_client_returnData'] = $selector;
            }
            catch(Exception $e){
                $GLOBALS['mini_db_client_returnData'] = $e->getMessage();
            }
            finally{
                return $GLOBALS['mini_db_client_returnData'];
            }
        }



    }



    error_reporting(1);