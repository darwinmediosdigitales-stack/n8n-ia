<?php
defined( 'ABSPATH' ) || exit;

add_action( 'init', 'vpty_register_types' );

function vpty_register_types() {
	register_post_type(
		'vacante',
		array(
			'labels'        => array(
				'name'               => __( 'Vacantes', 'vacantespty' ),
				'singular_name'      => __( 'Vacante', 'vacantespty' ),
				'add_new'            => __( 'Añadir vacante', 'vacantespty' ),
				'add_new_item'       => __( 'Añadir nueva vacante', 'vacantespty' ),
				'edit_item'          => __( 'Editar vacante', 'vacantespty' ),
				'view_item'          => __( 'Ver vacante', 'vacantespty' ),
				'search_items'       => __( 'Buscar vacantes', 'vacantespty' ),
				'not_found'          => __( 'No hay vacantes', 'vacantespty' ),
				'all_items'          => __( 'Todas las vacantes', 'vacantespty' ),
				'featured_image'     => __( 'Logo de la empresa', 'vacantespty' ),
				'set_featured_image' => __( 'Subir logo de la empresa', 'vacantespty' ),
			),
			'public'        => true,
			'has_archive'   => 'empleos',
			'rewrite'       => array( 'slug' => 'empleo', 'with_front' => false ),
			'menu_icon'     => 'dashicons-id-alt',
			'menu_position' => 5,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'show_in_rest'  => true,
		)
	);

	register_taxonomy(
		'categoria_empleo',
		'vacante',
		array(
			'labels'            => array(
				'name'          => __( 'Categorías de empleo', 'vacantespty' ),
				'singular_name' => __( 'Categoría de empleo', 'vacantespty' ),
				'menu_name'     => __( 'Categorías', 'vacantespty' ),
			),
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'empleos-de', 'with_front' => false ),
		)
	);

	register_taxonomy(
		'provincia',
		'vacante',
		array(
			'labels'            => array(
				'name'          => __( 'Provincias', 'vacantespty' ),
				'singular_name' => __( 'Provincia', 'vacantespty' ),
			),
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'empleos-en', 'with_front' => false ),
		)
	);

	register_post_type(
		'banner',
		array(
			'labels'              => array(
				'name'               => __( 'Banners', 'vacantespty' ),
				'singular_name'      => __( 'Banner', 'vacantespty' ),
				'add_new'            => __( 'Añadir banner', 'vacantespty' ),
				'add_new_item'       => __( 'Añadir nuevo banner', 'vacantespty' ),
				'edit_item'          => __( 'Editar banner', 'vacantespty' ),
				'featured_image'     => __( 'Imagen del banner', 'vacantespty' ),
				'set_featured_image' => __( 'Subir imagen del banner', 'vacantespty' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-megaphone',
			'menu_position'       => 6,
			'supports'            => array( 'title', 'thumbnail' ),
		)
	);
}
