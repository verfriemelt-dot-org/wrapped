<?php

    namespace verfriemelt\wrapped\_\DataModel\View;

    use \verfriemelt\wrapped\_\DataModel\DataModel;

    class ViewDataModel
    extends DataModel {

        public static function truncate(): void {
            throw new Exception( 'delete not allowed' );
        }

        public function delete(): static {
            throw new Exception( 'delete not allowed' );
        }

        public function save(): static {
            throw new Exception( 'update not allowed' );
        }

    }
