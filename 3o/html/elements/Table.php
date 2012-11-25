<?php

require_once TRIO_DIR . '/whereis.php';

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
     * @var HtmlElement
     */
    private $thead;

    /**
     * @var HtmlElement
     */
    private $tfoot;

    /**
     * @var HtmlElement
     */
    private $tbody;

    /**
     * @var HtmlElement[][]
     */
    private $rows = array('head' => array(), 'foot' => array(), 'body' => array());

    /**
     * @var HtmlElement[][]
     */
    private $cells = array('head' => array(), 'foot' => array(), 'body' => array());
    private $caption;

    public function __construct($caption = '', $id = '') {
        parent::__construct("table", $id);

        $this->caption = new HtmlInline('caption', $this->getId() . '_caption');
        $this->caption->setText($caption);
        $this->thead = new HtmlTableHead($this->getId() . '_header');
        $this->tfoot = new HtmlTableFooter($this->getId() . '_footer');
        $this->tbody = new HtmlTableBody($this->getId() . '_body');

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
     * @return HtmlElement
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
     * Notice: Unlike the normal cell method, this one gets the column index first
     * @param int $col >0
     * @param int $row >0
     * @return HtmlElement
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
            $row = new HtmlTableRow($this->getId() . '_' . $region. '_row_' . $this->numRows[$region]);

            // create table cells for the row
            for ($col = 0; $col < $this->numCols; $col++) {
                $cell = new HtmlTableCell($cellTag, $this->getId() . '_cell_' . $this->numRows[$region] . 'x' . $col);
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
                $cell = new HtmlTableCell($cellTag, $this->getId() . '_cell_' . $rownr . 'x' . $colnr);
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
     * @return HtmlTableBody
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
     * @return HtmlTableFooter
     */
    public function getFooter() {
        return $this->getRegionParent(Table::FOOT);
    }
    
    /**
     * Get the object for the specified row in the given table region
     * @param int $index
     * @param string $region
     * @return HtmlTableRow
     */
    public function getRow($index, $region = self::BODY)
    {
        if ($index > $this->numRows[$region]) {
            $this->expandRows($index - $this->numRows[$region], $region);
        }
        return $this->rows[$region][$index - 1];
    }

}

class HtmlTableHead extends HtmlElement {

    public function __construct($id = '') {
        parent::__construct('thead', $id);
    }

}

class HtmlTableBody extends HtmlElement {

    public function __construct($id = '') {
        parent::__construct('tbody', $id);
    }

}

class HtmlTableFooter extends HtmlElement {

    public function __construct($id = '') {
        parent::__construct('tfoot', $id);
    }

}

class HtmlTableRow extends HtmlElement {

    public function __construct($id = '') {
        parent::__construct('tr', $id);
    }

}

class HtmlTableCell extends HtmlElement {

    public function __construct($type = Table::NORMAL_CELL, $id = '') {
        parent::__construct((Table::HEAD_CELL == $type) ? 'th' : 'td', $id);
    }

}