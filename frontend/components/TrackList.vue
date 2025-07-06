<template>
  <div>
    <!-- Empty State -->
    <div v-if="tracks.length === 0" class="text-center py-12">
      <div class="bg-white/10 backdrop-blur-sm rounded-lg p-8 max-w-md mx-auto">
        <h3 class="text-xl font-semibold text-white mb-2">No tracks yet</h3>
        <button 
          @click="$emit('add-track')"
          class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200"
        >
          Add Track
        </button>
      </div>
    </div>

    <!-- Tracks Table -->
    <div v-else class="bg-white/90 backdrop-blur-sm rounded-xl shadow-xl overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Track List ({{ tracks.length }} tracks)</h2>
      </div>
      
      <!-- Desktop Table -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Track
              </th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Artist
              </th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Duration
              </th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                ISRC
              </th>
              <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr 
              v-for="track in tracks" 
              :key="track.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ track.title }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-600">{{ track.artist }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ formatDuration(track.duration) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="track.isrc" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono bg-purple-100 text-purple-800">
                  {{ track.isrc }}
                </span>
                <span v-else class="text-sm text-gray-400">—</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button 
                  @click="$emit('edit-track', track)"
                  class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-lg transition-colors"
                >
                  Edit
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Cards -->
      <div class="md:hidden">
        <div class="p-4 space-y-4">
          <div 
            v-for="track in tracks" 
            :key="track.id"
            class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm"
          >
            <div class="flex justify-between items-start mb-3">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ track.title }}</h3>
                <p class="text-gray-600">{{ track.artist }}</p>
              </div>
              <button 
                @click="$emit('edit-track', track)"
                class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-lg transition-colors text-sm"
              >
               Edit
              </button>
            </div>
            <div class="flex items-center space-x-4 text-sm">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ formatDuration(track.duration) }}
              </span>
              <span v-if="track.isrc" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono bg-purple-100 text-purple-800">
                {{ track.isrc }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Props
defineProps({
  tracks: {
    type: Array,
    required: true
  }
})

// Emits
defineEmits(['add-track', 'edit-track'])

// Format duration from seconds to MM:SS
const formatDuration = (seconds) => {
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}
</script> 