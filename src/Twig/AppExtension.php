<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('filter_subcomments', [$this, 'filterSubComments']),
        ];
    }

    public function filterSubComments($comments)
    {
        $commentsArray = $comments->toArray();
        return array_filter($commentsArray, function ($comment) {
            return strlen($comment->getDescription()) > 0;
        });
    }
}

?>