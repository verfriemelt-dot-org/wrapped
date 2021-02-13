<div class="form-group form-group-{{ name }} {{ cssClasses }}">

    {{ if='displayLabel' }}
    <label class="form-label-{{ name }}" for="{{ name }}">{{ label }}</label>
    {{ /if='displayLabel' }}

    <textarea
        id="{{ name }}"
        class="form-control form-control-{{ name }}"
        name="{{ name }}"
        {{ if='placeholder' }} placeholder='{{ placeholder }}' {{ /if='placeholder' }}
        >{{ value }}</textarea>
</div>