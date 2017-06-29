<div class="form-group form-group-{{ name }} {{ cssClasses }}">
	
	<label>
    	<input
        	type="{{ type }}"
        	id="{{ name }}"
        	class="input-{{ name }}"
        	name="{{ name }}"
        	{{ if='placeholder' }} placeholder='{{ placeholder }}' {{ /if='placeholder' }}
        	{{ if='checked' }} checked="checked" {{ /if='checked'}}
        >
		<span>
    		{{ if='displayLabel' }}
        		{{ label }}
    		{{ /if='displayLabel' }}
    	</span>
    </label>

</div>