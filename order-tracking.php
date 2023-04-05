<?php
/*
 * Plugin Name: Tracking manual de reserva en 24KMastering
 * Plugin URI: https://singular.design
 * Description: Este plugin permite hacer seguimiento manual de reservas en 24KMastering.
 * Version: 1.0.0
 * Author: Rudy Rodriguez
 * Author URI: https://github.com/rrsingulardev/
 * Requires at least: 6.0
 * Requires PHP: 5.6
 
------------------------------------------------------------------------
Copyright 2009-2023 Rocketgenius, Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses.
*/

 // Crear taxonomías personalizadas para el estado de seguimiento
function crear_taxonomias_seguimiento() {
    $labels = array(
        'name'                       => __( 'Estados de seguimiento', 'woocommerce' ),
        'singular_name'              => __( 'Estado de seguimiento', 'woocommerce' ),
        'menu_name'                  => __( 'Estados de seguimiento', 'woocommerce' ),
        'all_items'                  => __( 'Todos los estados de seguimiento', 'woocommerce' ),
        'parent_item'                => __( 'Estado de seguimiento padre', 'woocommerce' ),
        'parent_item_colon'          => __( 'Estado de seguimiento padre:', 'woocommerce' ),
        'new_item_name'              => __( 'Nombre del nuevo estado de seguimiento', 'woocommerce' ),
        'add_new_item'               => __( 'Agregar nuevo estado de seguimiento', 'woocommerce' ),
        'edit_item'                  => __( 'Editar estado de seguimiento', 'woocommerce' ),
        'update_item'                => __( 'Actualizar estado de seguimiento', 'woocommerce' ),
        'view_item'                  => __( 'Ver estado de seguimiento', 'woocommerce' ),
        'separate_items_with_commas' => __( 'Separar estados de seguimiento con comas', 'woocommerce' ),
        'add_or_remove_items'        => __( 'Agregar o quitar estados de seguimiento', 'woocommerce' ),
        'choose_from_most_used'      => __( 'Elegir de los estados de seguimiento más usados', 'woocommerce' ),
        'popular_items'              => __( 'Estados de seguimiento populares', 'woocommerce' ),
        'search_items'               => __( 'Buscar estados de seguimiento', 'woocommerce' ),
        'not_found'                  => __( 'No se encontraron estados de seguimiento.', 'woocommerce' ),
        'no_terms'                   => __( 'No hay estados de seguimiento', 'woocommerce' ),
        'items_list'                 => __( 'Lista de estados de seguimiento', 'woocommerce' ),
        'items_list_navigation'      => __( 'Navegación de la lista de estados de seguimiento', 'woocommerce' ),
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => false,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => false,
        'show_tagcloud'              => false,
    );
    register_taxonomy( 'estado_seguimiento', array( 'shop_order' ), $args );
}
add_action( 'init', 'crear_taxonomias_seguimiento', 0 );

// Agregar campo personalizado de seguimiento en la página de edición de la orden
function agregar_campo_seguimiento() {
    global $post;
 
    echo '<div class="form-field">
            <label for="seguimiento">'.__('Seguimiento').' </label>
            <select name="seguimiento" id="seguimiento">
                <option value="received" '.selected(get_post_meta($post->ID, 'seguimiento', true), 'received', false).'>'.__('Received').'</option>
                <option value="in progress" '.selected(get_post_meta($post->ID, 'seguimiento', true), 'in progress', false).'>'.__('In progress').'</option>
                <option value="in Review" '.selected(get_post_meta($post->ID, 'seguimiento', true), 'in Review', false).'>'.__('In Review').'</option>
                <option value="done" '.selected(get_post_meta($post->ID, 'seguimiento', true), 'done', false).'>'.__('Dn Review').'</option>
            </select>
        </div>';
}
add_action( 'woocommerce_admin_order_data_after_order_details', 'agregar_campo_seguimiento' );

// Guardar el estado de seguimiento en la orden
function guardar_estado_seguimiento( $post_id ) {
    $seguimiento = isset( $_POST['seguimiento'] ) ? $_POST['seguimiento'] : '';
    update_post_meta( $post_id, 'seguimiento', sanitize_text_field( $seguimiento ) );
}
add_action( 'woocommerce_process_shop_order_meta', 'guardar_estado_seguimiento', 10, 1 );

 // Agregar nueva columna a la tabla de órdenes en la página de la cuenta del usuario
function agregar_columna_seguimiento_en_lista_ordenes( $columns ) {
    $columns['order_seguimiento'] = __('Job Tracking Info');
    return $columns;
}
add_filter( 'woocommerce_my_account_my_orders_columns', 'agregar_columna_seguimiento_en_lista_ordenes' );

// Mostrar el campo de seguimiento en la página de órdenes de la cuenta del usuario
function mostrar_seguimiento_en_lista_ordenes( $order ) {
    $seguimiento = get_post_meta( $order->get_id(), 'seguimiento', true );
    if ( $seguimiento ) {
        echo '<div class="order-seguimiento"><strong>'.__('Status').':</strong> '.$seguimiento.'</div>';
    }
}
add_action( 'woocommerce_my_account_my_orders_column_order_seguimiento', 'mostrar_seguimiento_en_lista_ordenes', 10, 1 );

// Agregar campo de seguimiento a los correos electrónicos de notificación de WooCommerce
function agregar_seguimiento_en_email( $keys ) {
    $keys['{seguimiento}'] = 'Seguimiento';
    return $keys;
}
add_filter( 'woocommerce_email_order_meta_keys', 'agregar_seguimiento_en_email' );

// Mostrar campo de seguimiento en los correos electrónicos de notificación de WooCommerce
function mostrar_seguimiento_en_email( $order, $sent_to_admin, $plain_text, $email ) {
    $seguimiento = get_post_meta( $order->get_id(), 'seguimiento', true );
    if ( $seguimiento ) {
        echo '<div style="margin-bottom: 10px;"><strong>'.__('Seguimiento').':</strong> '.$seguimiento.'</div>';
    }
}
add_action( 'woocommerce_email_order_meta', 'mostrar_seguimiento_en_email', 10, 4 );

