import { ref, computed } from 'vue';

/**
 * Composable for bulk selection logic with checkboxes.
 * @param {import('vue').Ref<Object[]>} items - reactive array of selectable items
 * @returns {{
 *   selectedIds: import('vue').Ref<number[]>,
 *   selectedRequests: import('vue').ComputedRef<Object[]>,
 *   selectedHours: import('vue').ComputedRef<string>,
 *   isAllSelected: import('vue').ComputedRef<boolean>,
 *   isIndeterminate: import('vue').ComputedRef<boolean>,
 *   toggleSelect: (id: number) => void,
 *   toggleAll: (checked: boolean) => void,
 *   clearSelection: () => void,
 * }}
 */
export function useBulkSelection(items) {
    const selectedIds = ref([]);

    const selectedRequests = computed(() => {
        return items.value.filter(r => selectedIds.value.includes(r.id));
    });

    const selectedHours = computed(() => {
        return selectedRequests.value
            .reduce((sum, r) => sum + (r.overtime?.hours ?? 0), 0)
            .toFixed(2);
    });

    const isAllSelected = computed(() => {
        return items.value.length > 0 && selectedIds.value.length === items.value.length;
    });

    const isIndeterminate = computed(() => {
        return selectedIds.value.length > 0 && selectedIds.value.length < items.value.length;
    });

    function toggleSelect(id) {
        const index = selectedIds.value.indexOf(id);
        if (index === -1) {
            selectedIds.value.push(id);
        } else {
            selectedIds.value.splice(index, 1);
        }
    }

    function toggleAll(checked) {
        selectedIds.value = checked ? items.value.map(r => r.id) : [];
    }

    function clearSelection() {
        selectedIds.value = [];
    }

    return {
        selectedIds,
        selectedRequests,
        selectedHours,
        isAllSelected,
        isIndeterminate,
        toggleSelect,
        toggleAll,
        clearSelection,
    };
}
