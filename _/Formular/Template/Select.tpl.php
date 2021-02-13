<div class="form-group form-group-{{ name }} {{ cssClasses }}">

    {{ if='displayLabel' }}
    <label class="form-label-{{ name }}" for="{{ name }}">{{ label }}</label>
    {{ /if='displayLabel' }}

    <select id="{{ name }}" class="form-control input-{{ name }}" name="{{ postname }}">

        {{ repeater='options' }}

        {{ if='openOptGroup' }}
        <optgroup label="{{ optGroupName }}">
            {{ /if='openOptGroup' }}

            {{ if='option' }}
            <option value="{{ value }}" {{ if='selected'}} selected='selected'{{ /if="selected"}} >{{ name }}</option>
            {{ /if='option' }}

            {{ if='closeOptGroup' }}
        </OptGroup>
        {{ /if='closeOptGroup' }}


        {{ /repeater='options' }}

    </select>
</div>
