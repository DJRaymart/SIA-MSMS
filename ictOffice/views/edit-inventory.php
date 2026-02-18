
<div id="editModal" class="ict-modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
  <div class="ict-modal-content bg-white rounded-xl shadow-lg w-96 p-6 relative max-h-[90vh] overflow-y-auto">
    <h3 class="text-xl font-semibold mb-4">Edit Inventory Item</h3>
    <form id="editInventoryForm" class="grid grid-cols-2 gap-4">

      <div class="col-span-2">
        <label for="editItemName" class="block font-medium mb-1">Item</label>
        <input id="editItemName" type="text" required name="item_name"
          class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div class="col-span-2">
        <label for="editItemModelNumber" class="block font-medium mb-1">Model Number</label>
        <input id="editItemModelNumber" type="text" required name="model_no"    
          class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div class="col-span-2">
        <label for="editItemDescription" class="block font-medium mb-1">Description</label>
        <input id="editItemDescription" type="text" required name="description"
          class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div class="col-span-2">
        <label for="editItemCategory" class="block font-medium mb-1">Category</label>
        <select id="editItemCategory" required name="category_id"
          class="w-full border border-gray-300 rounded px-3 py-2">
          <option value="">Select Category</option>
        </select>
      </div>

      <div class="col-span-2">
        <label for="editItemLocation" class="block font-medium mb-1">Location</label>
        <select id="editItemLocation" required class="w-full border border-gray-300 rounded px-3 py-2" 
          name="location_id">
          <option value="">Select location</option>
        </select>
      </div>

      <div>
        <label for="editItemQuantity" class="block font-medium mb-1">Quantity</label>
        <input id="editItemQuantity" type="number" min="0" required name="quantity"
          class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div>
        <label for="editItemDateAdded" class="block font-medium mb-1">Date Added</label>
        <input id="editItemDateAdded" type="date" required name="date_added"
          class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div class="col-span-2">
        <label for="editItemRemarks" class="block font-medium mb-1">Remarks</label>
        <textarea id="editItemRemarks" rows="4" class="w-full border border-gray-300 rounded px-3 py-2 resize-y"
          placeholder="Enter remarks here..." required name="remarks"></textarea>
      </div>

      <!-- Buttons -->
      <div class="col-span-2 flex justify-end gap-3 pt-2">
        <button type="button" id="cancelEditBtn" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">
          Cancel
        </button>
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
          Save
        </button>
      </div>
    </form>

    <button id="closeEditModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-lg font-bold" aria-label="Close Modal">&times;</button>
  </div>
</div>

