<!-- Modal สำหรับดูเอกสารใบยินยอม -->
<div id="viewConsentModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header" style="background: #ecfdf5; border-bottom: 2px solid #bbf7d0;">
            <h3 style="color: #065f46; display: flex; align-items: center; gap: 0.5rem;">
                📄 เอกสารคำขอและใบยินยอมสินเชื่อบุคคล
            </h3>
            <button type="button" class="close-btn" id="closeViewConsentModal" aria-label="Close modal" style="color: #065f46;">&times;</button>
        </div>
        <div class="modal-body scrollable" style="background: #fafafa;">
            <div id="viewConsentContent" style="padding: 1.5rem; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <!-- Content populated by JS -->
            </div>
        </div>
        <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; background: #ffffff; display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" class="action-btn outline" id="closeViewConsentFooter">ปิดหน้าต่าง</button>
        </div>
    </div>
</div>
