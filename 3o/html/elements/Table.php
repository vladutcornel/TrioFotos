<?php

namespace trio\html;
require_once \TRIO_DIR.'/framework.php';

/**
 * A HTML table.
 * It includes simple manipulation of the Table cells and 3 sections (thead, tbody, tfoot)
 * @author Cornel Borina <cornel@scoalaweb.com>
 * @package 3oLibrary
 * @subpackage HTML
 */
class Table extends HtmlElement {

    const HEAD = "head";
    const BODY = "body";
    const FOOT = "foot";

    // Table cell type
    const NORMAL_CELL = 'td';
    const HEAD_CELL = 'th';

    /**
     * @var int[]
     */
    private $numRows = array('head' => 0, 'foot' => 0, 'body' => 0);

    /**
     * @var int
     */
    private $numCols = 0;

    /**
     * @var TableHead
     */
    private $thead;

    /**
     * @var TableFooter
     */
    private $tfoot;

    /**
     * @var TableBody
     */
    private $tbody;

    /**
     * @var TableRow[][]
     */
    private $rows = array(self::HEAD => array(), self::FOOT => array(), self::BODY => array());

    /**
     * @var TableCell[][]
     */
    private $cells = array(self::HEAD => array(), self::FOOT => array(), self::BODY => array());
    private $caption;

    public function __construct($caption = '', $id = '') {
        parent::__construct("table", $id);

        $this->caption = new Inline('caption', $this->getId() . '_caption');
        $this->caption->setText($caption);
        $this->thead = new TableHead($this->getId() . '_header');
        $this->tfoot = new TableFooter($this->getId() . '_footer');
        $this->tbody = new TableBody($this->getId() . '_body');

        $this->addChild($this->thead);
        $this->addChild($this->tbody);
        $this->addChild($this->tfoot);
    }

    /**
     * Gets the HtmlElement for the specified cell.
     * The table is automaticaly expanded to match the size
     * @param int $row >0
     * @param int $col >0
     * @param string $region Table::HEAD, Table::BODY or Table::FOOT
     * @return TableCell
     */
    public function cell($row = 1, $col = 1, $region = Table::BODY) {
        if ($row > $this->numRows[$region]) {
            $this->expandRows($row - $this->numRows[$region], $region);
        }

        if ($col > $this->numCols) {
            $this->expandCells($col - $this->numCols);
        }
        return $this->cells[$region][$row - 1][$col - 1];
    }
    
    /**
     * Shorthand getter for head cells.
     * Notice: Unlike the normal cell method, this one gets the column index 
     * first since table headers are usually one row 
     * @param int $col >0
     * @param int $row >0
     * @return TableCell
     */
    public function head_cell($col = 1,$row = 1) {
        return $this->cell($row, $col,Table::HEAD);
    }

    /**
     * Expands the number of rows of thespcified region
     * @param int $delta the number of rows to be added
     */
    private function expandRows($delta, $region) {
        $parent = $this->getRegionParent($region);
        $cellTag = Table::NORMAL_CELL;
        if (Table::HEAD == $region) {
            $cellTag = Table::HEAD_CELL;
        }

        for ($i = 0; $i < $delta; $i++) {
            // create table row
            $row = new TableRow($this->getId() . '_' . $region. '_row_' . $this->numRows[$region]);

            // create table cells for the row
            for ($col = 0; $col < $this->numCols; $col++) {
                $cell = new TableCell($this,$region, $this->numRows[$region], $col, $cellTag, $this->getId() . '_cell_' . $this->numRows[$region] . 'x' . $col);
                $this->cells[$region][$this->numRows[$region]][$col] = $cell;
                $row->addChild($cell);
            }

            // register the row
            $this->rows[$region][$this->numRows[$region]] = $row;
            $parent->addChild($row);

            // next row
            $this->numRows[$region]++;
        }
    }

    /**
     * Expands the number of columns for the specified region or for the entire table
     * @param int $delta the number of columns to be added
     * @param string|null $region A valid table region or null to expand all
     */
    private function expandCells($delta, $region = null) {
        if (is_null($region)) {
            $this->expandCells($delta, Table::HEAD);
            $this->expandCells($delta, Table::BODY);
            $this->expandCells($delta, Table::FOOT);
            $this->numCols += $delta;
            return;
        }

        $cellTag = Table::NORMAL_CELL;
        if (Table::HEAD == $region) {
            $cellTag = Table::HEAD_CELL;
        }

        for ($rownr = 0; $rownr < $this->numRows[$region]; $rownr++) {
            $row = $this->rows[$region][$rownr];
            for ($col = 0; $col < $delta; $col++) {
                $colnr = $this->numCols + $col;
                $cell = new TableCell($this,$region, $rownr, $colnr, $cellTag, $this->getId() . '_cell_' . $rownr . 'x' . $colnr);
                $this->cells[$region][$rownr][$colnr] = $cell;
                $row->addChild($cell);
            }
        }
    }

    /**
     * @return HtmlElement the corespunding thead,tbody or tfoot
     */
    private function getRegionParent($region) {
        switch ($region) {
            case Table::HEAD:
                return $this->thead;
            case Table::FOOT:
                return $this->tfoot;
            case Table::BODY:
            default:
                return $this->tbody;
        }
    }

    /**
     * Get the Table body element
     * @return TableBody
     */
    public function getBody() {
        return $this->getRegionParent(Table::BODY);
    }

    /**
     * Get the html table element
     * @return HtmlTableHeader
     */
    public function getHeader() {
        return $this->getRegionParent(Table::HEAD);
    }

    /**
     * Get the table footer element
     * @return TableFooter
     */
    public function getFooter() {
        return $this->getRegionParent(Table::FOOT);
    }
    
    /**
     * Get the object for the specified row in the given table region
     * @param int $index
     * @param string $region
     * @return TableRow
     */
    public function getRow($index, $region = self::BODY)
    {
        if ($index > $this->numRows[$region]) {
            $this->expandRows($index - $this->numRows[$region], $region);
        }
        return $this->rows[$region][$index - 1];
    }

    public function cell_row_span($row, $col, $span, $region = self::BODY)
    {
        if ($span < 1)
            throw new UnexpectedValueException('The span of a cell should be a number greater than 0');
        if (!$this->cell($row, $col, $region)->canDisplay())
        {
            $this->cell($row, $col, $region)->setAttribute('rowspan', $span);
            return;
        }
        // the colspan of the element
        $colspan = $this->cell($row, $col, $region)->getColSpan();
        // initial row span
        $rowspan = $this->cell($row, $col, $region)->getRowSpan();
        
        // calculate the last index
        $stop_index = $row + $span;
        
        $stop_col_index = $col + $colspan;
        
        //
        for ($row_index = $row; $row_index < $stop_index; $row_index ++)
        {
            for ($col_index = $col; $col_index < $stop_col_index; $col_index ++)
                $this->cell($row_index, $col_index, $region)->hide();
        }
        
        
        $this->cell($row, $col, $region)->show();
        $this->cell($row, $col, $region)->setAttribute('rowspan', $span);
        
        // show other previously hidden cells
        $show_stop = $stop_index + $rowspan;
        for ($row_index = $stop_index; $row_index <= $show_stop && $row_index <= $this->numRows[$region]; $row_index++)
        {
            for ($col_index = $col; $col_index < $stop_col_index; $col_index ++)
            {
                $this->cell($row_index, $col_index, $region)->show();
            }
        }
        
        // regenerate rowspan for previously hidden elements
        for ($row_index = $stop_index; $row_index <= $show_stop && $row_index <= $this->numRows[$region]; $row_index++)
        {
            for ($col_index = $col; $col_index < $stop_col_index; $col_index ++)
            {
                
                // stop at the first cell that has it's own row span
                if ($this->cell($row_index, $col_index, $region)->getRowSpan() > 1){
                    $this->cell($row_index, $col_index, $region)->rowspan($this->cell($row_index, $col_index, $region)->getRowSpan());
                }
            }
        }
    }
    
    public function cell_col_span($row, $col, $span, $region = self::BODY)
    {
        if ($span < 1)
            throw new \UnexpectedValueException('The span of a cell should be a number greater than 0');
        if (!$this->cell($row, $col, $region)->canDisplay())
        {
            $this->cell($row, $col, $region)->setAttribute('colspan', $span);
            return;
        }
        // the colspan of the element
        $colspan = $this->cell($row, $col, $region)->getColSpan();
        // initial row span
        $rowspan = $this->cell($row, $col, $region)->getRowSpan();
        
        // calculate the last index
        $stop_index = $col + $span;
        
        // the colspan of the element
        $stop_row_index = $row + $rowspan;
        
        //
        for ($col_index = $col; $col_index < $stop_index; $col_index ++)
        {
            for ($row_index = $row; $row_index < $stop_row_index; $row_index ++)
                $this->cell($row_index, $col_index, $region)->hide();
        }
        
        $this->cell($row, $col, $region)->show();
        $this->cell($row, $col, $region)->setAttribute('colspan', $span);
        
        // show other previously hidden cells
        $show_stop = $stop_index + $colspan;
        for ($col_index = $stop_index; $col_index <= $show_stop && $col_index <= $this->numCols; $col_index++)
        {
            for ($row_index = $row; $row_index < $stop_row_index; $row_index ++)
                $this->cell($row_index, $col_index, $region)->show();
        }
        
         // regenerate col for previously hidden elements
        for ($col_index = $stop_index; $col_index <= $show_stop && $col_index <= $this->numRows[$region]; $col_index++)
        {
            for ($row_index = $row; $row_index < $stop_row_index; $row_index ++)
            {
                
                // stop at the first cell that has it's own row span
                if ($this->cell($row_index, $col_index, $region)->getColSpan() > 1){
                    $this->cell($row_index, $col_index, $region)->colspan($this->cell($row_index, $col_index, $region)->getColSpan());
                }
            }
        }
    }
}

class TableHead extends HtmlElement {

    public function __construct($id = '') {
        parent::__construct('thead', $id);
    }

}

class TableBody extends HtmlElement {

    public function __construct($id = '') {
        parent::__construct('tbody', $id);
    }

}

class TableFooter extends HtmlElement {

    public function __construct($id = '') {
        parent::__construct('tfoot', $id);
    }

}

class TableRow extends HtmlElement {

    public function __construct($id = '') {
        parent::__construct('tr', $id);
    }

}

class TableCell extends HtmlElement {
    /**
     *
     * @var Table
     */
    private $table = null;
    
    /**
     * The position of the cell's row in the table
     * @var int 
     */
    private $row_index = 0;
    
    /**
     * The position of the cell in the row
     * @var int 
     */
    private $col_index = 0;
    
    /**
     * The region from the table the cell is in
     * @var type 
     */
    private $region = Table::BODY;
    
    /**
     * 
     * @param Table $table
     * @param string $region
     * @param int $row
     * @param int $col
     * @param string $type
     * @param string $id
     */
    public function __construct($table, $region, $row, $col, $type = Table::NORMAL_CELL, $id = '') {
        parent::__construct((Table::HEAD_CELL == $type) ? 'th' : 'td', $id);
        
        $this->table = $table;
        $this->region = $region;
        $this->row_index = $row;
        $this->col_index = $col;
    }
    
    /**
     * Set the span of the cell in one method call
     * @param int $rows
     * @param int $cols
     */
    public function span($rows = 1, $cols = 1){
        $this->rowspan($rows);
        $this->colspan($cols);
        return $this;
    }
    public function rowspan($rows)
    {
        if ($rows < 1)
            throw new \UnexpectedValueException('The span of a cell should be a number greater than 0');
        $this->table->cell_row_span($this->row_index + 1, $this->col_index + 1, $rows, $this->region);
        return $this;
    }
    
    public function colspan($cols)
    {
        if ($cols < 1)
            throw new \UnexpectedValueException('The span of a cell should be a number greater than 0');
        $this->table->cell_col_span($this->row_index + 1, $this->col_index + 1, $cols, $this->region);
        return $this;
    }
    
    public function getRowSpan(){
        if (!$this->getAttribute('rowspan'))
        {
            return 1;
        }
        
        return \intval($this->getAttribute('rowspan'));
    }

    public function getColSpan(){
        if (!$this->getAttribute('colspan'))
        {
            return 1;
        }
        
        return \intval($this->getAttribute('colspan'));
    }
}