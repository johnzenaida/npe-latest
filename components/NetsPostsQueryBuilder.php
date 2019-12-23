<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 26.03.2019
 * Time: 10:00
 */

abstract class NetsPostsQueryBuilder {
	protected $query = '';

	public abstract function include(array $included);

	public abstract function exclude(array $excluded);

	public function get_query(){
		return $this->query;
	}

}