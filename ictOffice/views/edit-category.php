<div id="editCategoryModal" class="ict-modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
  <div class="ict-modal-content bg-white rounded-xl shadow-lg w-96 p-6 relative max-h-[90vh] overflow-y-auto">
    <h3 class="text-xl font-semibold mb-4">Edit Category</h3>
    <form id="editCategoryForm" class="space-y-4">
      <div>
        <label for="editCategoryID" class="block font-medium mb-1">ID</label>
        <input id="editCategoryID" type="text" required class="w-full border border-gray-300 rounded px-3 py-2" name="categoryID"/>
      </div>
      <div>
        <label for="editCategoryName" class="block font-medium mb-1">Category Name</label>
        <input id="editCategoryName" type="text" required class="w-full border border-gray-300 rounded px-3 py-2" name="categoryName"/>
      </div>
      
      <div class="flex justify-end gap-3">
        <button type="button" id="cancelEditCategoryBtn" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Edit</button>
      </div>
    </form>
    <button id="closeEditCategoryModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-lg font-bold" aria-label="Close Modal">&times;</button>
  </div>
</div>