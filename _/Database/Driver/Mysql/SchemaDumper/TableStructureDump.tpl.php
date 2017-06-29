<?php

    $schema =  \Wrapped\_\Database\Driver\Mysql\Schema::create( "{{ tableName }}" );
    {{ repeater="fields" }}
    $schema
        ->{{ columnType }}("{{ columnName }}"){{ if="default" }}
        ->defaultValue("{{ columnDefault }}"){{ /if="default" }}{{ if="length" }}
        ->length("{{ length }}"){{ /if="length" }}{{ if="enumOptions" }}
        ->enumOptions({{ !enumOptions }}){{ /if="enumOptions" }}{{ if="isNullable" }}
        ->nullable(){{ /if="isNullable" }}{{ if="unsigned" }}
        ->unsigned(){{ /if="unsigned" }};
    {{ /repeater="fields" }}
    {{ repeater="indexData" }}
    $schema
        {{ if='primary'}}->{{ indexType }}({{ !indexColumns}});
        {{ else='primary'}}->{{ indexType }}("{{ indexName }}",{{ !indexColumns}});{{ /if='primary'}}
    {{ /repeater="indexData" }}

    return $schema;