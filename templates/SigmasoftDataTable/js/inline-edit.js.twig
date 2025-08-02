/**
 * SigmasoftDataTableBundle - Inline Edit Manager V2
 * 
 * JavaScript moderne pour gestion de l'édition inline avec:
 * - Debouncing intelligent
 * - Retry automatique  
 * - Gestion des erreurs
 * - Indicateurs visuels
 * - Support multi-types de champs
 * 
 * @author Gédéon Makela <g.makela@sigmasoft-solution.com>
 * @version 2.3.7
 */

class InlineEditManagerV2 {
    constructor(options = {}) {
        this.options = {
            debounceDelay: 1000,
            maxRetries: 3,
            retryDelay: 1500,
            apiEndpoint: '/product/update-field', // Default endpoint
            ...options
        };
        
        this.activeRequests = new Map();
        this.retryCounters = new Map();
        
        this.init();
    }

    init() {
        this.bindEvents();
        console.log('🚀 InlineEditManagerV2 initialized');
    }

    bindEvents() {
        // Délégation d'événements pour les champs éditables
        document.addEventListener('change', this.handleFieldChange.bind(this));
        document.addEventListener('blur', this.handleFieldBlur.bind(this));
        document.addEventListener('keydown', this.handleKeyDown.bind(this));
        
        // Support pour auto-resize des textareas
        document.addEventListener('input', this.handleTextareaResize.bind(this));
    }

    handleFieldChange(event) {
        const field = event.target;
        if (!this.isEditableField(field)) return;
        
        this.scheduleUpdate(field);
    }

    handleFieldBlur(event) {
        const field = event.target;
        if (!this.isEditableField(field)) return;
        
        // Force immediate save on blur
        this.cancelScheduledUpdate(field);
        this.updateField(field);
    }

    handleKeyDown(event) {
        const field = event.target;
        if (!this.isEditableField(field)) return;
        
        // Save on Enter (except for textareas)
        if (event.key === 'Enter' && field.tagName !== 'TEXTAREA') {
            event.preventDefault();
            this.cancelScheduledUpdate(field);
            this.updateField(field);
        }
        
        // Cancel on Escape
        if (event.key === 'Escape') {
            this.revertField(field);
        }
    }

    handleTextareaResize(event) {
        const field = event.target;
        if (field.tagName === 'TEXTAREA' && field.classList.contains('auto-resize')) {
            this.autoResizeTextarea(field);
        }
    }

    isEditableField(element) {
        return element && element.classList.contains('editable-field');
    }

    scheduleUpdate(field) {
        const fieldKey = this.getFieldKey(field);
        
        // Cancel previous scheduled update
        this.cancelScheduledUpdate(field);
        
        // Schedule new update
        const timeoutId = setTimeout(() => {
            this.updateField(field);
        }, this.options.debounceDelay);
        
        this.activeRequests.set(fieldKey + '_timeout', timeoutId);
    }

    cancelScheduledUpdate(field) {
        const fieldKey = this.getFieldKey(field);
        const timeoutId = this.activeRequests.get(fieldKey + '_timeout');
        
        if (timeoutId) {
            clearTimeout(timeoutId);
            this.activeRequests.delete(fieldKey + '_timeout');
        }
    }

    async updateField(field) {
        const fieldKey = this.getFieldKey(field);
        const entityId = field.dataset.entityId;
        const fieldName = field.dataset.fieldName;
        const newValue = this.getFieldValue(field);
        const originalValue = field.dataset.originalValue;

        // Skip if value hasn't changed
        if (newValue === originalValue) {
            return;
        }

        // Cancel if there's already a request for this field
        if (this.activeRequests.has(fieldKey)) {
            return;
        }

        try {
            this.showSavingIndicator(field);
            
            const requestData = {
                id: entityId,
                field: fieldName,
                value: newValue
            };

            console.log('📤 Sending update:', requestData);

            const response = await this.sendUpdateRequest(requestData, fieldKey);
            
            if (response.success) {
                this.handleUpdateSuccess(field, response);
            } else {
                throw new Error(response.message || 'Unknown error');
            }
            
        } catch (error) {
            console.error('❌ Update failed:', error);
            this.handleUpdateError(field, error);
        } finally {
            this.hideSavingIndicator(field);
            this.activeRequests.delete(fieldKey);
        }
    }

    async sendUpdateRequest(data, fieldKey) {
        // Get update URL from data attributes or use default
        const container = document.querySelector('[data-update-field-url]');
        const updateUrl = container?.dataset.updateFieldUrl || this.options.apiEndpoint;
        
        const controller = new AbortController();
        this.activeRequests.set(fieldKey, controller);

        const response = await fetch(updateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(data),
            signal: controller.signal
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return await response.json();
    }

    handleUpdateSuccess(field, response) {
        console.log('✅ Update successful:', response);
        
        // Update original value
        const newValue = this.getFieldValue(field);
        field.dataset.originalValue = newValue;
        
        // Reset retry counter
        const fieldKey = this.getFieldKey(field);
        this.retryCounters.delete(fieldKey);
        
        // Hide error indicator
        this.hideErrorIndicator(field);
        
        // Optional: Show success feedback briefly
        this.showSuccessIndicator(field);
    }

    handleUpdateError(field, error) {
        console.error('❌ Update error:', error);
        
        const fieldKey = this.getFieldKey(field);
        const retryCount = this.retryCounters.get(fieldKey) || 0;
        
        if (retryCount < this.options.maxRetries) {
            // Schedule retry
            this.retryCounters.set(fieldKey, retryCount + 1);
            console.log(`⏰ Scheduling retry ${retryCount + 1}/${this.options.maxRetries}`);
            
            setTimeout(() => {
                this.updateField(field);
            }, this.options.retryDelay);
        } else {
            // Max retries reached
            console.error('💀 Max retries reached for field:', fieldKey);
            this.showErrorIndicator(field, error.message);
            this.retryCounters.delete(fieldKey);
        }
    }

    getFieldKey(field) {
        return `${field.dataset.entityId}_${field.dataset.fieldName}`;
    }

    getFieldValue(field) {
        if (field.type === 'checkbox') {
            return field.checked;
        }
        return field.value;
    }

    revertField(field) {
        const originalValue = field.dataset.originalValue;
        
        if (field.type === 'checkbox') {
            field.checked = originalValue === 'true';
        } else {
            field.value = originalValue;
        }
        
        // Cancel any pending updates
        this.cancelScheduledUpdate(field);
        const fieldKey = this.getFieldKey(field);
        const controller = this.activeRequests.get(fieldKey);
        if (controller) {
            controller.abort();
            this.activeRequests.delete(fieldKey);
        }
        
        this.hideErrorIndicator(field);
        this.hideSavingIndicator(field);
    }

    // Visual Indicators
    showSavingIndicator(field) {
        const wrapper = field.closest('.editable-cell-wrapper');
        if (wrapper) {
            const indicator = wrapper.querySelector('.saving-indicator');
            if (indicator) {
                indicator.classList.remove('d-none');
            }
        }
    }

    hideSavingIndicator(field) {
        const wrapper = field.closest('.editable-cell-wrapper');
        if (wrapper) {
            const indicator = wrapper.querySelector('.saving-indicator');
            if (indicator) {
                indicator.classList.add('d-none');
            }
        }
    }

    showErrorIndicator(field, message = '') {
        const wrapper = field.closest('.editable-cell-wrapper');
        if (wrapper) {
            const indicator = wrapper.querySelector('.error-indicator');
            if (indicator) {
                indicator.classList.remove('d-none');
                if (message) {
                    const icon = indicator.querySelector('i');
                    if (icon) {
                        icon.title = `Erreur: ${message}`;
                    }
                }
            }
        }
        
        // Add error styling to field
        field.classList.add('is-invalid');
    }

    hideErrorIndicator(field) {
        const wrapper = field.closest('.editable-cell-wrapper');
        if (wrapper) {
            const indicator = wrapper.querySelector('.error-indicator');
            if (indicator) {
                indicator.classList.add('d-none');
            }
        }
        
        // Remove error styling
        field.classList.remove('is-invalid');
    }

    showSuccessIndicator(field) {
        // Brief green border to indicate success
        field.classList.add('is-valid');
        setTimeout(() => {
            field.classList.remove('is-valid');
        }, 2000);
    }

    autoResizeTextarea(textarea) {
        // Reset height to auto to get the correct scrollHeight
        textarea.style.height = 'auto';
        
        // Set height to scrollHeight
        const minHeight = parseInt(textarea.dataset.rows || 3) * 1.5; // Approximate line height
        const newHeight = Math.max(textarea.scrollHeight, minHeight * 16); // 16px per line approx
        
        textarea.style.height = newHeight + 'px';
    }

    // Public methods
    destroy() {
        // Cancel all active requests
        this.activeRequests.forEach((value, key) => {
            if (key.endsWith('_timeout')) {
                clearTimeout(value);
            } else if (value instanceof AbortController) {
                value.abort();
            }
        });
        
        this.activeRequests.clear();
        this.retryCounters.clear();
        
        // Remove event listeners would require storing references
        console.log('🧹 InlineEditManagerV2 destroyed');
    }

    // Static factory method
    static create(options = {}) {
        return new InlineEditManagerV2(options);
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize if we have editable fields
    if (document.querySelector('.editable-field')) {
        window.sigmasoftInlineEdit = InlineEditManagerV2.create();
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = InlineEditManagerV2;
}

// Global namespace
window.InlineEditManagerV2 = InlineEditManagerV2;