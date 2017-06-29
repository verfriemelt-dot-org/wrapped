<?php

    namespace Wrapped\_\Database\Driver\Mysql\InformationSchema;

    use \Wrapped\_\Database\Database;
    use \Wrapped\_\DataModel\DatabaseOverride;
    use \Wrapped\_\DataModel\DataModel;
    use \Wrapped\_\DataModel\TablenameOverride;

    class Columns
    extends DataModel
    implements DatabaseOverride, TablenameOverride {

        public $TABLE_CATALOG,
            $TABLE_SCHEMA,
            $TABLE_NAME,
            $COLUMN_NAME,
            $ORDINAL_POSITION,
            $COLUMN_DEFAULT,
            $IS_NULLABLE,
            $DATA_TYPE,
            $CHARACTER_MAXIMUM_LENGTH,
            $CHARACTER_OCTET_LENGTH,
            $NUMERIC_PRECISION,
            $NUMERIC_SCALE,
            $DATETIME_PRECISION,
            $CHARACTER_SET_NAME,
            $COLLATION_NAME,
            $COLUMN_TYPE,
            $COLUMN_KEY,
            $EXTRA,
            $PRIVILEGES,
            $COLUMN_COMMENT,
            $GENERATION_EXPRESSION;

        public static function fetchDatabase() {
            return Database::getConnection( "information-schema" );
        }

        public static function fetchTablename() {
            return "COLUMNS";
        }

        public function getTABLE_CATALOG() {
            return $this->TABLE_CATALOG;
        }

        public function getTABLE_SCHEMA() {
            return $this->TABLE_SCHEMA;
        }

        public function getTABLE_NAME() {
            return $this->TABLE_NAME;
        }

        public function getCOLUMN_NAME() {
            return $this->COLUMN_NAME;
        }

        public function getORDINAL_POSITION() {
            return $this->ORDINAL_POSITION;
        }

        public function getCOLUMN_DEFAULT() {
            return $this->COLUMN_DEFAULT;
        }

        public function getIS_NULLABLE() {
            return $this->IS_NULLABLE;
        }

        public function getDATA_TYPE() {
            return $this->DATA_TYPE;
        }

        public function getCHARACTER_MAXIMUM_LENGTH() {
            return $this->CHARACTER_MAXIMUM_LENGTH;
        }

        public function getCHARACTER_OCTET_LENGTH() {
            return $this->CHARACTER_OCTET_LENGTH;
        }

        public function getNUMERIC_PRECISION() {
            return $this->NUMERIC_PRECISION;
        }

        public function getNUMERIC_SCALE() {
            return $this->NUMERIC_SCALE;
        }

        public function getDATETIME_PRECISION() {
            return $this->DATETIME_PRECISION;
        }

        public function getCHARACTER_SET_NAME() {
            return $this->CHARACTER_SET_NAME;
        }

        public function getCOLLATION_NAME() {
            return $this->COLLATION_NAME;
        }

        public function getCOLUMN_TYPE() {
            return $this->COLUMN_TYPE;
        }

        public function getCOLUMN_KEY() {
            return $this->COLUMN_KEY;
        }

        public function getEXTRA() {
            return $this->EXTRA;
        }

        public function getPRIVILEGES() {
            return $this->PRIVILEGES;
        }

        public function getCOLUMN_COMMENT() {
            return $this->COLUMN_COMMENT;
        }

        public function getGENERATION_EXPRESSION() {
            return $this->GENERATION_EXPRESSION;
        }

        public function setTABLE_CATALOG( $TABLE_CATALOG ) {
            $this->TABLE_CATALOG = $TABLE_CATALOG;
            return $this;
        }

        public function setTABLE_SCHEMA( $TABLE_SCHEMA ) {
            $this->TABLE_SCHEMA = $TABLE_SCHEMA;
            return $this;
        }

        public function setTABLE_NAME( $TABLE_NAME ) {
            $this->TABLE_NAME = $TABLE_NAME;
            return $this;
        }

        public function setCOLUMN_NAME( $COLUMN_NAME ) {
            $this->COLUMN_NAME = $COLUMN_NAME;
            return $this;
        }

        public function setORDINAL_POSITION( $ORDINAL_POSITION ) {
            $this->ORDINAL_POSITION = $ORDINAL_POSITION;
            return $this;
        }

        public function setCOLUMN_DEFAULT( $COLUMN_DEFAULT ) {
            $this->COLUMN_DEFAULT = $COLUMN_DEFAULT;
            return $this;
        }

        public function setIS_NULLABLE( $IS_NULLABLE ) {
            $this->IS_NULLABLE = $IS_NULLABLE;
            return $this;
        }

        public function setDATA_TYPE( $DATA_TYPE ) {
            $this->DATA_TYPE = $DATA_TYPE;
            return $this;
        }

        public function setCHARACTER_MAXIMUM_LENGTH( $CHARACTER_MAXIMUM_LENGTH ) {
            $this->CHARACTER_MAXIMUM_LENGTH = $CHARACTER_MAXIMUM_LENGTH;
            return $this;
        }

        public function setCHARACTER_OCTET_LENGTH( $CHARACTER_OCTET_LENGTH ) {
            $this->CHARACTER_OCTET_LENGTH = $CHARACTER_OCTET_LENGTH;
            return $this;
        }

        public function setNUMERIC_PRECISION( $NUMERIC_PRECISION ) {
            $this->NUMERIC_PRECISION = $NUMERIC_PRECISION;
            return $this;
        }

        public function setNUMERIC_SCALE( $NUMERIC_SCALE ) {
            $this->NUMERIC_SCALE = $NUMERIC_SCALE;
            return $this;
        }

        public function setDATETIME_PRECISION( $DATETIME_PRECISION ) {
            $this->DATETIME_PRECISION = $DATETIME_PRECISION;
            return $this;
        }

        public function setCHARACTER_SET_NAME( $CHARACTER_SET_NAME ) {
            $this->CHARACTER_SET_NAME = $CHARACTER_SET_NAME;
            return $this;
        }

        public function setCOLLATION_NAME( $COLLATION_NAME ) {
            $this->COLLATION_NAME = $COLLATION_NAME;
            return $this;
        }

        public function setCOLUMN_TYPE( $COLUMN_TYPE ) {
            $this->COLUMN_TYPE = $COLUMN_TYPE;
            return $this;
        }

        public function setCOLUMN_KEY( $COLUMN_KEY ) {
            $this->COLUMN_KEY = $COLUMN_KEY;
            return $this;
        }

        public function setEXTRA( $EXTRA ) {
            $this->EXTRA = $EXTRA;
            return $this;
        }

        public function setPRIVILEGES( $PRIVILEGES ) {
            $this->PRIVILEGES = $PRIVILEGES;
            return $this;
        }

        public function setCOLUMN_COMMENT( $COLUMN_COMMENT ) {
            $this->COLUMN_COMMENT = $COLUMN_COMMENT;
            return $this;
        }

        public function setGENERATION_EXPRESSION( $GENERATION_EXPRESSION ) {
            $this->GENERATION_EXPRESSION = $GENERATION_EXPRESSION;
            return $this;
        }

    }
