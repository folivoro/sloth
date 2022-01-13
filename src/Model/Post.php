<?php

namespace Sloth\Model;

use Corcel\Model\Post as CorcelPost;

class Post extends CorcelPost
{
    /**
     * @return string
     */
    public function getContentAttribute()
    {
        return apply_filters('the_content', $this->post_content);
    }
}
