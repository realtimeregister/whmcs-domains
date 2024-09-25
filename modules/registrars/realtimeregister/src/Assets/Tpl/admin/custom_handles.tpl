<div class="modal fade" id="propertiesModal" tabindex="-1" role="dialog" aria-labelledby="propertiesModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="propertiesModalLabel">{$LANG.rtr.custom_handles.custom_properties}</h4>
            </div>
            <div class="modal-body">
                <p class="bg-danger" id="rtr-error-message" style="padding: 1rem; display: none"><i class="fas fa-exclamation-triangle"></i> {$LANG.rtr.custom_handles.error}</p>
                <form id="propertiesForm">
                    <input type="hidden" name="typeOfForm" value="customPropertiesForm">
                    <input type="hidden" name="action" value="propertiesMutate"/>
                    <input type="hidden" name="module" value="realtimeregister" />
                    <div id="waiting-for-input">
                        <i class="fad fa-cog fa-spin fa-5x center-block"></i>
                        <br>
                        {$LANG.rtr.custom_handles.please_wait}
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{$LANG.rtr.custom_handles.close}</button>
                <button type="button" class="btn btn-primary" id="saveCustomProperties">{$LANG.rtr.custom_handles.save}</button>
            </div>
        </div>
    </div>
</div>
