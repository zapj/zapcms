<?php

namespace zap\helpers;

class Pagination
{
    /** @var int Current page number */
    protected $currentPage = 1;

    /** @var int Number of items per page */
    protected $perPage = 10;

    /** @var int Total number of items */
    protected $total = 0;

    /** @var string Base path for pagination URLs */
    protected $path = '';

    /** @var int Number of links to show on each side of current page */
    protected $onEachSide = 3;

    /** @var array Default CSS classes */
    protected $classes = [
        'nav'         => 'pagination',
        'item'        => 'page-item',
        'link'        => 'page-link',
        'active'      => 'active',
        'disabled'    => 'disabled',
    ];

    /** @var array Callbacks for custom rendering */
    protected $callbacks = [];

    /**
     * Static factory
     */
    public static function make(int $total, int $perPage = 10, int $currentPage = 1): self
    {
        return new self($total, $perPage, $currentPage);
    }

    /**
     * Constructor supports two calling styles:
     *   New: Pagination(total, perPage, currentPage)
     *   Old: Pagination(currentPage, perPage, queryParams)  — for backward compatibility
     */
    public function __construct(int $a = 0, int $b = 10, $c = 1)
    {
        if (is_array($c)) {
            // Old API: (int $currentPage, int $perPage, array $queryParams)
            $currentPage = max(1, $a);
            $perPage     = max(1, $b);
            $queryParams = $c;
            $total       = 0;
            $this->fromQueryParams($queryParams);
        } else {
            // New API: (int $total, int $perPage, int $currentPage)
            $total       = max(0, $a);
            $perPage     = max(1, $b);
            $currentPage = max(1, (int)$c);
        }

        $this->total       = $total;
        $this->perPage     = $perPage;
        $this->currentPage = $currentPage;
    }

    /**
     * Extract pagination state from GET query parameters.
     */
    protected function fromQueryParams(array $params): void
    {
        $pathParts = [];
        foreach ($params as $key => $value) {
            if ($key === 'page') {
                continue;
            }
            $pathParts[] = $key . '=' . urlencode((string)$value);
        }
        if ($pathParts) {
            $this->path = '?' . implode('&', $pathParts);
        }
    }

    // ─── Fluent Setters ────────────────────────────────────────

    public function withPath(string $path): self
    {
        $this->path = rtrim($path, '/');
        return $this;
    }

    public function setCurrentPage(int $page): self
    {
        $this->currentPage = max(1, $page);
        return $this;
    }

    public function setPerPage(int $perPage): self
    {
        $this->perPage = max(1, $perPage);
        return $this;
    }

    public function setTotal(int $total): self
    {
        $this->total = max(0, $total);
        return $this;
    }

    public function onEachSide(int $count): self
    {
        $this->onEachSide = max(0, $count);
        return $this;
    }

    public function setClasses(array $classes): self
    {
        $this->classes = array_merge($this->classes, $classes);
        return $this;
    }

    // ─── Getters ───────────────────────────────────────────────

    public function currentPage(): int
    {
        return min($this->currentPage, $this->totalPages());
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Alias for perPage() — backward compatibility.
     */
    public function getLimit(): int
    {
        return $this->perPage;
    }

    /**
     * Return the SQL offset = (currentPage - 1) * perPage.
     */
    public function getOffset(): int
    {
        return max(0, ($this->currentPage - 1) * $this->perPage);
    }

    public function total(): int
    {
        return $this->total;
    }

    public function totalPages(): int
    {
        if ($this->perPage < 1) {
            return 0;
        }
        return (int)ceil($this->total / $this->perPage);
    }

    public function hasPages(): bool
    {
        return $this->totalPages() > 1;
    }

    public function hasMorePages(): bool
    {
        return $this->currentPage() < $this->totalPages();
    }

    public function isFirstPage(): bool
    {
        return $this->currentPage() <= 1;
    }

    public function isLastPage(): bool
    {
        return $this->currentPage() >= $this->totalPages();
    }

    public function firstItem(): int
    {
        if ($this->total === 0) {
            return 0;
        }
        return ($this->currentPage() - 1) * $this->perPage + 1;
    }

    public function lastItem(): int
    {
        return min(
            $this->currentPage() * $this->perPage,
            $this->total
        );
    }

    public function offset(): int
    {
        return ($this->currentPage() - 1) * $this->perPage;
    }

    // ─── URL Builders ──────────────────────────────────────────

    public function url(int $page): string
    {
        if ($page <= 0 || $page > $this->totalPages()) {
            return '#';
        }

        $path = $this->path ?: '';
        if (str_contains($path, '?')) {
            if (str_contains($path, '{page}')) {
                return str_replace('{page}', (string)$page, $path);
            }
            return $path . '&page=' . $page;
        }
        return $path . '/' . $page;
    }

    public function previousPageUrl(): string
    {
        if ($this->isFirstPage()) {
            return '#';
        }
        return $this->url($this->currentPage() - 1);
    }

    public function nextPageUrl(): string
    {
        if ($this->isLastPage()) {
            return '#';
        }
        return $this->url($this->currentPage() + 1);
    }

    public function firstPageUrl(): string
    {
        return $this->url(1);
    }

    public function lastPageUrl(): string
    {
        return $this->url($this->totalPages());
    }

    // ─── Data / API ────────────────────────────────────────────

    /**
     * Get pagination metadata for API / JSON responses.
     */
    public function meta(): array
    {
        return [
            'current_page'   => $this->currentPage(),
            'per_page'       => $this->perPage,
            'total'          => $this->total,
            'total_pages'    => $this->totalPages(),
            'from'           => $this->firstItem(),
            'to'             => $this->lastItem(),
            'has_more'       => $this->hasMorePages(),
            'next_page_url'  => $this->nextPageUrl(),
            'prev_page_url'  => $this->previousPageUrl(),
            'first_page_url' => $this->firstPageUrl(),
            'last_page_url'  => $this->lastPageUrl(),
        ];
    }

    public function toArray(): array
    {
        return $this->meta();
    }

    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->meta(), $options);
    }

    // ─── Rendering ─────────────────────────────────────────────

    /**
     * Build an HTML link element.
     */
    protected function buildLink(int $page, string $text = null): string
    {
        $text   = $text ?? (string)$page;
        $url    = $this->url($page);
        $active = ($page === $this->currentPage())
            ? ' ' . ($this->classes['active'] ?? 'active')
            : '';

        return sprintf(
            '<li class="%s%s"><a class="%s" href="%s">%s</a></li>',
            $this->h($this->classes['item'] ?? 'page-item'),
            $active,
            $this->h($this->classes['link'] ?? 'page-link'),
            $this->h($url),
            $text
        );
    }

    /**
     * Build a disabled link (for prev/next when unavailable).
     */
    protected function buildDisabled(string $text): string
    {
        return sprintf(
            '<li class="%s %s"><span class="%s">%s</span></li>',
            $this->h($this->classes['item'] ?? 'page-item'),
            $this->h($this->classes['disabled'] ?? 'disabled'),
            $this->h($this->classes['link'] ?? 'page-link'),
            $text
        );
    }

    /**
     * Get the array of page numbers to display.
     */
    protected function elements(): array
    {
        $total  = $this->totalPages();
        $current = $this->currentPage();

        if ($total <= 1) {
            return [];
        }

        $pages = [];

        // First page
        if ($current > $this->onEachSide + 2) {
            $pages[] = 1;
            if ($current > $this->onEachSide + 3) {
                $pages[] = '...';
            }
        }

        // Sliding window
        $start = max(1, $current - $this->onEachSide);
        $end   = min($total, $current + $this->onEachSide);

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        // Last page
        if ($current < $total - $this->onEachSide - 1) {
            if ($current < $total - $this->onEachSide - 2) {
                $pages[] = '...';
            }
            $pages[] = $total;
        }

        return $pages;
    }

    /**
     * Render pagination HTML (Bootstrap 5 compatible by default).
     */
    public function render($prevLabel = '&laquo;', string $nextLabel = '&raquo;'): string
    {
        // Backward compatibility: old signature render($onEachSide, $navClass, $itemClass, $linkClass)
        if (is_int($prevLabel)) {
            $this->onEachSide($prevLabel);
            if (is_string($nextLabel) && $nextLabel !== '') {
                $this->classes['nav'] = $nextLabel;
            }
            $itemClass = func_num_args() > 2 ? (string)func_get_arg(2) : '';
            $linkClass = func_num_args() > 3 ? (string)func_get_arg(3) : '';
            if ($itemClass !== '') {
                $this->classes['item'] = $itemClass;
            }
            if ($linkClass !== '') {
                $this->classes['link'] = $linkClass;
            }
            $prevLabel = '&laquo;';
            $nextLabel = '&raquo;';
        }

        if (!$this->hasPages()) {
            return '';
        }

        $html = '<nav><ul class="' . $this->h($this->classes['nav'] ?? 'pagination') . '">';

        // Previous
        if ($this->isFirstPage()) {
            $html .= $this->buildDisabled($prevLabel);
        } else {
            $html .= $this->buildLink($this->currentPage() - 1, $prevLabel);
        }

        // Page numbers
        foreach ($this->elements() as $element) {
            if ($element === '...') {
                $html .= $this->buildDisabled('...');
            } else {
                $html .= $this->buildLink($element);
            }
        }

        // Next
        if ($this->isLastPage()) {
            $html .= $this->buildDisabled($nextLabel);
        } else {
            $html .= $this->buildLink($this->currentPage() + 1, $nextLabel);
        }

        $html .= '</ul></nav>';

        if (isset($this->callbacks['render'])) {
            $html = call_user_func($this->callbacks['render'], $html, $this);
        }

        return $html;
    }

    /**
     * Render Bootstrap 4 pagination (adjusts class names).
     */
    public function renderBootstrap4(): string
    {
        $backup = $this->classes;
        $this->classes = array_merge($this->classes, [
            'nav'      => 'pagination',
            'item'     => 'page-item',
            'link'     => 'page-link',
            'active'   => 'active',
            'disabled' => 'disabled',
        ]);
        $html = $this->render();
        $this->classes = $backup;
        return $html;
    }

    /**
     * Render a simple "Prev / Next" only pagination.
     */
    public function renderSimple(string $prevLabel = '&laquo; 上一页', string $nextLabel = '下一页 &raquo;'): string
    {
        if (!$this->hasPages()) {
            return '';
        }

        $html = '<nav><ul class="' . $this->h($this->classes['nav'] ?? 'pagination') . '">';

        if ($this->isFirstPage()) {
            $html .= $this->buildDisabled($prevLabel);
        } else {
            $html .= $this->buildLink($this->currentPage() - 1, $prevLabel);
        }

        if ($this->isLastPage()) {
            $html .= $this->buildDisabled($nextLabel);
        } else {
            $html .= $this->buildLink($this->currentPage() + 1, $nextLabel);
        }

        $html .= '</ul></nav>';
        return $html;
    }

    /**
     * Render total count display.
     */
    public function renderCount(): string
    {
        return sprintf(
            '共 %d 条，每页 %d 条，当前第 %d / %d 页',
            $this->total,
            $this->perPage,
            $this->currentPage(),
            $this->totalPages()
        );
    }

    /**
     * Register a custom render callback.
     */
    public function withRender(callable $callback): self
    {
        $this->callbacks['render'] = $callback;
        return $this;
    }

    // ─── Internals ─────────────────────────────────────────────

    protected function h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
