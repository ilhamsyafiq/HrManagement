<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Submit New Claim') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4">
                    <div class="flex items-center gap-3 mb-2">
                        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-red-700">Please fix the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside text-sm text-red-600 ml-8 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Main Card --}}
            <form method="POST" action="{{ route('claims.store') }}" enctype="multipart/form-data" id="claimForm">
                @csrf

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                    {{-- Section Header --}}
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <h3 class="text-base font-semibold text-gray-900">Claim Details</h3>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">

                        {{-- Title --}}
                        <div>
                            <x-input-label for="title" :value="__('Claim Title')" class="mb-1.5" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full rounded-xl" :value="old('title')" required placeholder="e.g. March 2026 Travel Expenses" />
                        </div>

                        {{-- Description --}}
                        <div>
                            <x-input-label for="description" :value="__('Description (optional)')" class="mb-1.5" />
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm transition-colors duration-200 resize-none" placeholder="Brief description of the claim...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Items Section --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">

                    {{-- Section Header --}}
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            <h3 class="text-base font-semibold text-gray-900">Claim Items</h3>
                        </div>
                        <button type="button" onclick="addItemRow()" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-xl transition-all duration-200 hover:shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Item
                        </button>
                    </div>

                    {{-- Add Item Form (inline) --}}
                    <div id="addItemSection" class="p-6 border-b border-gray-100 bg-blue-50/30">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                                <input type="text" id="newItemDescription" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-sm" placeholder="e.g. Grab ride to client">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Amount (RM) *</label>
                                <input type="number" id="newItemAmount" step="0.01" min="0.01" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-sm" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Expense Date *</label>
                                <input type="date" id="newItemDate" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                <select id="newItemCategory" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-sm">
                                    <option value="">Select Category</option>
                                    <option value="Transport">Transport</option>
                                    <option value="Meal">Meal</option>
                                    <option value="Accommodation">Accommodation</option>
                                    <option value="Office Supplies">Office Supplies</option>
                                    <option value="Medical">Medical</option>
                                    <option value="Training">Training</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Receipt (optional)</label>
                                <input type="file" id="newItemReceipt" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                                <input type="text" id="newItemNotes" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-sm" placeholder="Any additional notes">
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="button" onclick="confirmAddItem()" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 px-4 rounded-xl transition-all duration-200 hover:shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Add to List
                            </button>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100" id="itemsTable">
                            <thead>
                                <tr class="bg-gray-50/80">
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount (RM)</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Receipt</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50" id="itemsBody">
                                <tr id="noItemsRow">
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">
                                        No items added yet. Use the form above to add expense items.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Total --}}
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-700">Total Amount:</span>
                        <span class="text-lg font-bold text-gray-900" id="totalAmount">RM 0.00</span>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-4 mt-6">
                    <a href="{{ route('claims.index') }}" class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all duration-200">
                        Cancel
                    </a>
                    <button type="submit" id="submitBtn" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2.5 px-6 rounded-xl transition-all duration-200 hover:shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Create Claim
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        let itemIndex = 0;
        let totalAmount = 0;
        let fileInputs = {};

        function addItemRow() {
            document.getElementById('addItemSection').scrollIntoView({ behavior: 'smooth', block: 'center' });
            document.getElementById('newItemDescription').focus();
        }

        function confirmAddItem() {
            const description = document.getElementById('newItemDescription').value.trim();
            const amount = parseFloat(document.getElementById('newItemAmount').value);
            const date = document.getElementById('newItemDate').value;
            const category = document.getElementById('newItemCategory').value;
            const notes = document.getElementById('newItemNotes').value.trim();
            const receiptInput = document.getElementById('newItemReceipt');

            if (!description) { alert('Please enter a description.'); return; }
            if (!amount || amount <= 0) { alert('Please enter a valid amount.'); return; }
            if (!date) { alert('Please select an expense date.'); return; }
            if (!category) { alert('Please select a category.'); return; }

            // Remove "no items" row
            const noItemsRow = document.getElementById('noItemsRow');
            if (noItemsRow) noItemsRow.remove();

            const tbody = document.getElementById('itemsBody');
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50/60 transition-colors duration-150';
            row.id = 'itemRow_' + itemIndex;

            let receiptName = 'None';
            if (receiptInput.files.length > 0) {
                receiptName = receiptInput.files[0].name;
                // Store the file for form submission
                fileInputs[itemIndex] = receiptInput.files[0];
            }

            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(description)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${amount.toFixed(2)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${date}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${escapeHtml(category)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(receiptName)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <button type="button" onclick="removeItem(${itemIndex}, ${amount})" class="text-red-600 hover:text-red-800 font-medium transition-colors duration-150">Remove</button>
                </td>
            `;

            // Add hidden inputs for this item
            const hiddenInputs = document.createElement('div');
            hiddenInputs.id = 'itemInputs_' + itemIndex;
            hiddenInputs.innerHTML = `
                <input type="hidden" name="items[${itemIndex}][description]" value="${escapeAttr(description)}">
                <input type="hidden" name="items[${itemIndex}][amount]" value="${amount}">
                <input type="hidden" name="items[${itemIndex}][expense_date]" value="${date}">
                <input type="hidden" name="items[${itemIndex}][category]" value="${escapeAttr(category)}">
                <input type="hidden" name="items[${itemIndex}][notes]" value="${escapeAttr(notes)}">
            `;
            document.getElementById('claimForm').appendChild(hiddenInputs);

            // Handle file input - create a new file input for the form
            if (receiptInput.files.length > 0) {
                const fileClone = receiptInput.cloneNode(true);
                fileClone.id = 'receiptInput_' + itemIndex;
                fileClone.name = 'items[' + itemIndex + '][receipt]';
                fileClone.style.display = 'none';
                document.getElementById('claimForm').appendChild(fileClone);
            }

            tbody.appendChild(row);

            totalAmount += amount;
            document.getElementById('totalAmount').textContent = 'RM ' + totalAmount.toFixed(2);

            // Reset fields
            document.getElementById('newItemDescription').value = '';
            document.getElementById('newItemAmount').value = '';
            document.getElementById('newItemDate').value = '';
            document.getElementById('newItemCategory').value = '';
            document.getElementById('newItemNotes').value = '';

            // Reset file input by replacing it
            const newFileInput = document.createElement('input');
            newFileInput.type = 'file';
            newFileInput.id = 'newItemReceipt';
            newFileInput.accept = '.pdf,.jpg,.jpeg,.png';
            newFileInput.className = receiptInput.className;
            receiptInput.parentNode.replaceChild(newFileInput, receiptInput);

            itemIndex++;
        }

        function removeItem(index, amount) {
            const row = document.getElementById('itemRow_' + index);
            const inputs = document.getElementById('itemInputs_' + index);
            const fileInput = document.getElementById('receiptInput_' + index);

            if (row) row.remove();
            if (inputs) inputs.remove();
            if (fileInput) fileInput.remove();

            totalAmount -= amount;
            if (totalAmount < 0) totalAmount = 0;
            document.getElementById('totalAmount').textContent = 'RM ' + totalAmount.toFixed(2);

            // Check if no items remain
            const tbody = document.getElementById('itemsBody');
            if (tbody.children.length === 0) {
                const noItemsRow = document.createElement('tr');
                noItemsRow.id = 'noItemsRow';
                noItemsRow.innerHTML = '<td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">No items added yet. Use the form above to add expense items.</td>';
                tbody.appendChild(noItemsRow);
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }

        function escapeAttr(text) {
            return text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        // Validate at least one item before submit
        document.getElementById('claimForm').addEventListener('submit', function(e) {
            const tbody = document.getElementById('itemsBody');
            const noItemsRow = document.getElementById('noItemsRow');
            if (noItemsRow || tbody.children.length === 0) {
                e.preventDefault();
                alert('Please add at least one item to the claim.');
            }
        });
    </script>
</x-app-layout>
