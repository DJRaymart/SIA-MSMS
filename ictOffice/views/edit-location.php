<div id="editLocationModal" class="ict-modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
  <div class="ict-modal-content bg-white rounded-xl shadow-lg w-96 p-6 relative max-h-[90vh] overflow-y-auto">
    <h3 class="text-xl font-semibold mb-4">Edit Location</h3>
    <form id="editLocationForm" class="space-y-4">
      <div>
        <label for="editLocationID" class="block font-medium mb-1">ID</label>
        <input id="editLocationID" type="text" required class="w-full border border-gray-300 rounded px-3 py-2" name="locationID"/>
      </div>
      <div>
        <label for="editLocationName" class="block font-medium mb-1">Location Name</label>
        <input id="editLocationName" type="text" required class="w-full border border-gray-300 rounded px-3 py-2" name="locationName"/>
      </div>
      
      <div class="flex justify-end gap-3">
        <button type="button" id="cancelEditLocationBtn" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Edit</button>
      </div>
    </form>
    <button id="closeEditLocationModalBtn" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-lg font-bold" aria-label="Close Modal">&times;</button>
  </div>
</div>