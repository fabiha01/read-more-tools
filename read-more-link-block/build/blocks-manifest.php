<?php
// This file is generated. Do not modify it manually.
return array(
	'read-more-link-block' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'create-block/read-more-link-block',
		'version' => '0.1.0',
		'title' => 'Read More Link Block',
		'category' => 'widgets',
		'icon' => 'text-page',
		'description' => 'Read more link block.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'attributes' => array(
			'postId' => array(
				'type' => 'number'
			),
			'postTitle' => array(
				'type' => 'string'
			),
			'postLink' => array(
				'type' => 'string'
			)
		),
		'textdomain' => 'read-more-link-block',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	)
);
