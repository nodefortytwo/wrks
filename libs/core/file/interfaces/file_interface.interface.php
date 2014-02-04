<?php

interface iFileInterface{
	function __construct($file);
	public function save();
	public function load();
	public function stream();
	public function delete();
	public function create($contents);
	public function copy($dest);
	public function rename($name);
	public function exists();
}