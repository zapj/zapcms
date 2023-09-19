<?php

namespace zap\helpers;

class Pagination
{
    public $limit;
    public $page;
    public $total;
    public $data;
    public $prevLink = 'javascript:void(0);';
    public $nextLink = 'javascript:void(0);';

    public function __construct($page = 1, $limit = 20, $data = array()) {
        $this->page = $page ? $page : 1;
        $this->limit = $limit;
        $this->data = $data;
    }

    public function setTotal($total) {
        $this->total = $total;
        return $this;
    }

    public function setData($data) {
        $this->data = $data;
        return $this;
    }

    public function getLimit() {
        return $this->limit;
    }

    public function getOffset() {
        return ceil(($this->page - 1) * $this->limit);
    }

    public function render($linkNum = 7, $list_class = 'pagination  justify-content-center',$li_class = 'page-item' ,$a_class = 'page-link') {
        if ($this->limit == 'all' || $this->limit == 0) {
            return '';
        }

        $last = ceil($this->total / $this->limit);
        $last = $last == 0 ? 1 : $last;
        $start = ( ( $this->page - $linkNum ) > 0 ) ? $this->page - $linkNum : 1;
        $end = ( ( $this->page + $linkNum ) < $last ) ? $this->page + $linkNum : $last;

        $html = '<nav aria-label="Page navigation"><ul class="' . $list_class . '">';

        if ($this->page == 1) {
            $class = 'disabled';
            $html .= '<li class="' . $li_class .'  ' . $class . '"><a class="'.$a_class.'" href="javascript:void(0);">&laquo;</a></li>';
        }

        if ($start > 1) {
            $html .= '<li class="' . $li_class .'"><a class="'.$a_class.'"  href="?' . $this->buildQuery(array('limit' => $this->limit, 'page' => 1)) . '">1</a></li>';
            $html .= '<li class="' . $li_class .' disabled" ><span>...</span></li>';
        }

        for ($i = $start; $i <= $end; $i++) {
            $class = ( $this->page == $i ) ? "active" : "";
            $html .= '<li class="' . $li_class .'  ' . $class . '"><a class="'.$a_class.'" href="?' . $this->buildQuery(array('limit' => $this->limit, 'page' => $i)) . '">' . $i . '</a></li>';
        }

        if ($end < $last) {
            $html .= '<li class="' . $li_class .' disabled"><span>...</span></li>';
            $html .= '<li class="' . $li_class .'" ><a  class="'.$a_class.'" href="?' . $this->buildQuery(array('limit' => $this->limit, 'page' => $last)) . '">' . $last . '</a></li>';
        }

        if ($this->page == $last) {
            $html .= '<li class="' . $li_class .' disabled"><a class="'.$a_class.'" href="javascript:void(0);">&raquo;</a></li>';
        }

        $html .= '</ul></nav>';

        return $html;
    }

    protected function buildQuery($data) {
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
        return http_build_query($this->data);
    }

    public function getPrevLink() {
        if ($this->page - 1) {
            $this->prevLink = '?' . $this->buildQuery(array('limit' => $this->limit, 'page' => $this->page - 1));
        }
        return $this->prevLink;
    }

    public function getNextLink() {
        $last = ceil($this->total / $this->limit);
        $last = $last == 0 ? 1 : $last;
        if ($this->page < $last) {
            $this->nextLink = '?' . $this->buildQuery(array('limit' => $this->limit, 'page' => $this->page + 1));
        }
        return $this->nextLink;
    }
}