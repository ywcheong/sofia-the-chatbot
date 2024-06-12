<?php

    class TableElement{
        private $_title = "";
        private $_titleArray = array();
        private $_contentArray = array();
        private $_focusindex = -1;
        
        public function __construct($title, $titleArray){
            if(count($titleArray) > 6) return;
            while(count($titleArray) < 6) array_push($titleArray, "");
            $this->_title = $title;
            $this->_titleArray = $titleArray;
        }

        public function addContent($contentElement, $isFocus = FALSE){
            if(count($contentElement) > 6) return;
            while(count($contentElement) < 6) array_push($contentElement, "");
            array_push($this->_contentArray, $contentElement);
            if($isFocus) $this->_focusindex = count($this->_contentArray) - 1;
        }

        public function isEmpty(){
            return (count($this->_contentArray) == 0);
        }
        
        public function generateTable($mode){
            
            {
                $rtn = ''.
                '<h2 style="margin-bottom: 10px;">'.$this->_title.'</h2>'.
                '<div class="table100 ver'.$mode.' m-b-50">'.
                '<div class="table100-head">'.
                '<table>'.
                '<thead>'.
                '<tr class="row100 head">'.
                '<th class="cell100 column1">'.$this->_titleArray[0].'</th>'.
                '<th class="cell100 column2">'.$this->_titleArray[1].'</th>'.
                '<th class="cell100 column3">'.$this->_titleArray[2].'</th>'.
                '<th class="cell100 column4">'.$this->_titleArray[3].'</th>'.
                '<th class="cell100 column5">'.$this->_titleArray[4].'</th>'.
                '<th class="cell100 column6">'.$this->_titleArray[5].'</th>'.
                '</tr>'.
                '</thead>'.
                '</table>'.
                '</div>'.
                '<div class="table100-body js-pscroll">'.
                '<table>'.
                '<tbody>';
                
                for($i=0; $i<count($this->_contentArray); $i++){
                    $rtn = $rtn .
                    '<tr class="row100 body" '.($this->_focusindex == $i ? 'id="tablefocus" ' : "").'>'.
                    '<td class="cell100 column1">'.$this->_contentArray[$i][0].'</td>'.
                    '<td class="cell100 column2">'.$this->_contentArray[$i][1].'</td>'.
                    '<td class="cell100 column3">'.$this->_contentArray[$i][2].'</td>'.
                    '<td class="cell100 column4">'.$this->_contentArray[$i][3].'</td>'.
                    '<td class="cell100 column5">'.$this->_contentArray[$i][4].'</td>'.
                    '<td class="cell100 column6">'.$this->_contentArray[$i][5].'</td>'.
                    '</tr>';
                }
                $rtn = $rtn.'</tbody></table></div></div>';
                return $rtn;

            }

        }
    }
    
    class TableList{
        private $_tablelist = array();
        private $_mode;
        
        public function __construct($mode){
            $this->_mode = (int)$mode;
            if($this->_mode <= 0 || $this->_mode >= 6) $this->_mode = 1;
        }

        public function addTable($tableClass){
            array_push($this->_tablelist, $tableClass);
        }

        public function generate(){
            $rtn = "";
            for($i=0; $i<count($this->_tablelist); $i++){
                $rtn = $rtn . $this->_tablelist[$i]->generateTable($this->_mode);
            }
            return $rtn;
        }
    }
?>