<?php

    namespace Wrapped\_\Database\Driver\Mysql\SchemaDumper;

    use \Wrapped\_\Template\Template;
    use \Wrapped\_\View\View;

    class TableStructureDump
    extends View {

        public function __construct( \Wrapped\_\Database\Driver\Mysql\SchemaDumper\SchemaDump $schema ) {

            $this->tpl = (new Template() )->setRawTemplate( file_get_contents( __DIR__ . "/TableStructureDump.tpl.php" ) );
            $this->tpl->set( "tableName", $schema->getTableName() );

            $fieldsR = $this->tpl->createRepeater( "fields" );

            foreach ( $schema->dumpStructure() as $column ) {

                if ( $column["DATA_TYPE"] == "enum" ) {

                    preg_match( "~\((.+)\)~", $column["COLUMN_TYPE"], $enumOptions );

                    $fieldsR->set( "columnType", strtolower( "enum" ) );
                    $fieldsR->setIf( "enumOptions" );
                    $fieldsR->set( "enumOptions", $enumOptions[1] );
                } else {
                    preg_match( "~([a-zA-Z]+)(?:\(([0-9,]+)\))?\s?(unsigned)?~", $column["COLUMN_TYPE"], $result );
                    $fieldsR->set( "columnType", strtolower( $result[1] ) );
                }

                $fieldsR->set( "columnName", $column["COLUMN_NAME"] );

                if ( $column["COLUMN_DEFAULT"] !== null ) {
                    $fieldsR->setIf( "default" );
                    $fieldsR->set( "columnDefault", $column["COLUMN_DEFAULT"] );
                }


                if ( isset( $result[2] ) ) {
                    $fieldsR->setIf( "length" );
                    $fieldsR->set( "length", $result[2] );
                }

                if ( isset( $result[3] ) ) {
                    $fieldsR->setIf( "unsigned" );
                }

                if ( $column["IS_NULLABLE"] == "YES" ) {
                    $fieldsR->setIf( "isNullable" );
                }

                if ( $column["EXTRA"] == "auto_increment" ) {
                    $fieldsR->set( "columnType", "increments" );
                }

                $fieldsR->save();
            }

            /// index data

            $indicies = [];

            foreach ( $schema->getIndicesStructure() as $index ) {

                if ( !isset( $indicies[$index["INDEX_NAME"]] ) ) {
                    if ( $index["INDEX_NAME"] == "PRIMARY" ) {
                        $indicies[$index["INDEX_NAME"]][0] = "primaryIndex";
                    } elseif ( $index["NON_UNIQUE"] == 1 ) {
                        $indicies[$index["INDEX_NAME"]][0] = "index";
                    } else {
                        $indicies[$index["INDEX_NAME"]][0] = "uniqueIndex";
                    }
                }

                $indicies[$index["INDEX_NAME"]][1][] = $index["COLUMN_NAME"];
            }

            $indexR = $this->tpl->createRepeater( "indexData" );
            foreach ( $indicies as $name => $index ) {

                $indexR->setIf( "primary", $index[0] == "primaryIndex" );

                $indexR->set( "indexName", $name );
                $indexR->set( "indexType", $index[0] );

                $columns = "'" . implode( "','", $index[1] ) . "'";

                $indexR->set( "indexColumns", $columns );
                $indexR->save();
            }
        }

    }
