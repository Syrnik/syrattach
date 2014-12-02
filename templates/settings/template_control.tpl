<div style="text-align: right;padding: 0.3em 0">
    <a href="javascript:void(0);" class="inline-link" id="{$settings.id}-restore-template"><i class="icon16 delete"></i> {_wp('Restore original template')}</a>
</div>
<div class="s-editor-core-wrapper">
    <textarea id="{$settings.id}" name="syrattach_template">{$template}</textarea>
</div>
<script type="text/javascript">
    $(function(){
        var c = CodeMirror.fromTextArea(document.getElementById('{$settings.id}'),{
            mode: "text/html",
            tabMode: "indent",
            height: "dynamic",
            lineWrapping: true
        });
        $("#{$settings.id}").change(function(){
            c.setValue($(this).val());
        });
        
        {if !$template_modified}
            $("#{$settings.id}-restore-template").hide();
        {/if}
        
        $("#{$settings.id}").closest("#plugins-settings-form").submit(function(){
            $("#{$settings.id}-restore-template").show();
        });
        
        $("#{$settings.id}-restore-template").click(function(){
            if(confirm("{_wp("Do you really want to reset the template to the original one? All changes you're made will lost!")|escape:javascript}") === true) {
                $.get("?plugin=syrattach&module=settings&action=originaltemplate", function(r){
                    if(r.status == 'ok'){
                        console.log(c);
                        $("#{$settings.id}").val(r.data.template);
                        c.setValue($("#{$settings.id}").val());
                    }
                });
            }
            return false;
        });
    });
</script>