<div class="form-group form-group-{{ name }} {{ cssClasses }}">

    {{ if='displayLabel' }}
        <label class="form-label-{{ name }}" for="{{ name }}">{{ label }}</label>
    {{ /if='displayLabel' }}

    <input
        type="{{ type }}"
        id="{{ name }}"
        class="form-control form-control-{{ name }}"
        name="{{ name }}"
        {{ if='readonly' }} readonly='readonly' {{ /if='readonly' }}
        {{ if='disabled' }} disabled='disabled' {{ /if='disabled' }}
        {{ if='placeholder' }} placeholder='{{ placeholder }}' {{ /if='placeholder' }}
        value="{{ value }}"
        {{ if='pattern' }}pattern="{{ pattern }}" title="{{ title }}"{{ /if='pattern' }} {{ if='required' }}required{{ /if='required' }}
        >
</div>