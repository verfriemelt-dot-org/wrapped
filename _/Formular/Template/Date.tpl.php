<div class="form-group form-group-{{ name }} {{ cssClasses }}">

    {{ if='displayLabel' }}
    <label class="form-label-{{ name }}" for="{{ name }}">{{ label }}</label>
    {{ /if='displayLabel' }}

    <input
        type="date"
        id="{{ name }}"
        class="form-control form-control-{{ name }}"
        name="{{ name }}"
        {{ if='readonly' }} readonly='readonly' {{ /if='readonly' }}
        {{ if='disabled' }} disabled='disabled' {{ /if='disabled' }}
        {{ if='placeholder' }} placeholder='{{ placeholder }}' {{ /if='placeholder' }}
        value="{{ value }}"
    >
</div>
