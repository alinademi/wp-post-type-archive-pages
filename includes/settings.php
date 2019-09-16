<?php

class PTAP_Settings {

    private $supported_post_types;

    function __construct() {

        add_action( 'admin_init', array( $this, 'add_settings_fields' ) );
        add_action( 'update_option_' . post_type_archive_pages()::CONFIG_KEY, array( $this, 'updated_option' ) );
        add_action( 'admin_init', array( $this, 'maybe_flush_rules' ) );

    }

    function add_settings_fields() {

        $this->supported_post_types = post_type_archive_pages()->get_supported_post_types();

        register_setting(
            'reading',
            post_type_archive_pages()::CONFIG_KEY
        );

        if ( count($this->supported_post_types) ) {

            add_settings_field(
                'archive-pages',
                'Archive Pages',
                array( $this, 'draw_fields' ),
                'reading'
            );

        }

    }

    function draw_fields() {
        ?>
        <fieldset>

            <?php foreach( $this->supported_post_types as $post_type ) : ?>

                <?php
                $field_name = post_type_archive_pages()::CONFIG_KEY . '[' . $post_type->name . ']';
                $field_label = $post_type->label;
                $field_value = post_type_archive_pages()->get_archive_page_id( $post_type->name )
                ?>

                <label for="<?php echo $field_name ?>">
                    <?php
                    printf(
                        __( $field_label . ': %s' ),
                        wp_dropdown_pages(
                            array(
                                'name'              => $field_name,
                                'echo'              => 0,
                                'show_option_none'  => __( '&mdash; Select &mdash;' ),
                                'option_none_value' => '0',
                                'selected'          => $field_value,
                            )
                        )
                    );
                    ?>
                </label><br>

            <?php endforeach ?>

        </fieldset>
        <?php
    }

    public function updated_option() {

        set_transient( 'ptap_flush_rules', 1 );

    }

    public function maybe_flush_rules() {

        if ( delete_transient('ptap_flush_rules') ) {
            flush_rewrite_rules();
        }

    }

}