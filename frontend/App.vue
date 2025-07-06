<template>
  <div class="min-h-screen bg-blue-500">
    <!-- Header -->
    <header class="bg-white/90 backdrop-blur-sm border-b border-white/20 shadow-lg">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div class="flex items-center space-x-3">
            <h1 class="text-2xl font-bold text-gray-800">Track Management</h1>
          </div>
          <button 
            @click="showAddModal = true"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5"
          >
            <span class="mr-2">+</span>
            Add Track
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-white"></div>
        <p class="mt-4 text-white text-lg">Loading tracks...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-12">
        <div class="bg-red-500/20 backdrop-blur-sm rounded-lg p-8 max-w-md mx-auto">
          <div class="text-4xl mb-4">⚠️</div>
          <h3 class="text-xl font-semibold text-white mb-2">Error Loading Tracks</h3>
          <p class="text-red-100 mb-4">{{ error }}</p>
          <button 
            @click="loadTracks"
            class="bg-white/20 hover:bg-white/30 text-white px-6 py-2 rounded-lg transition-colors"
          >
            Try Again
          </button>
        </div>
      </div>

      <!-- Track List -->
      <TrackList 
        v-else
        :tracks="tracks"
        @add-track="showAddModal = true"
        @edit-track="editTrack"
      />
    </main>

    <!-- Add Track Modal -->
    <TrackModal
      v-if="showAddModal"
      :track="null"
      mode="add"
      @close="showAddModal = false"
      @saved="onTrackSaved"
    />

    <!-- Edit Track Modal -->
    <TrackModal
      v-if="showEditModal"
      :track="editingTrack"
      mode="edit"
      @close="showEditModal = false"
      @saved="onTrackSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import TrackList from './components/TrackList.vue'
import TrackModal from './components/TrackModal.vue'
import { trackService } from './services/trackService'

// Reactive data
const tracks = ref([])
const loading = ref(false)
const error = ref(null)
const showAddModal = ref(false)
const showEditModal = ref(false)
const editingTrack = ref(null)

// Load tracks from API
const loadTracks = async () => {
  loading.value = true
  error.value = null
  
  try {
    tracks.value = await trackService.getTracks()
  } catch (err) {
    error.value = err.message || 'Failed to load tracks'
  } finally {
    loading.value = false
  }
}

// Edit track
const editTrack = (track) => {
  editingTrack.value = { ...track }
  showEditModal.value = true
}

// Handle track saved (add or edit)
const onTrackSaved = () => {
  showAddModal.value = false
  showEditModal.value = false
  editingTrack.value = null
  loadTracks() // Reload the list
}

// Load tracks on mount
onMounted(() => {
  loadTracks()
})
</script>

<style scoped>
</style>
