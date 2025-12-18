<script setup>
import { ref, reactive, onMounted, computed, watch } from "vue";
import "cally"; // Calendar library
import axios from "axios";
import CalendarSidebar from "@/components/Layout/CalendarSidebar.vue";
import { getPDFUrl } from "../../lib/supabase.js";

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL; // <-- add this

// ------------------ STATES ------------------
const calendarOpen = ref(false);
const isSidebarOpen = ref(false);

const registeredPosts = reactive({});
const appliedPosts = ref({});
const selectedCareerDetails = ref(null);
const recommendedTrainings = ref([]);
const loadingCareers = ref(false); // <-- new
const allCareers = ref([]);
const selectedCareerId = ref(null);
const showCareerPopup = ref(false);
const showTrainingModal = ref(false);
const selectedTraining = ref(null);
const trainingActionLoading = ref(false);
const trainingActionError = ref("");
const selectedPost = ref(null);
const posts = ref([]);
const myApplications = ref(new Set());
const myRegistrations = ref(new Set());
const myRegistrationsData = ref([]); // Store full registration data
const toasts = ref([]);
const loadingCardId = ref(null); // store the careerID of the card being clicked
const isSubmitting = ref(false);
const isCancelling = ref(false);

// ------------------ FETCH DATA ------------------
const events = ref({});
const selectedDate = ref("");
const dayEvents = ref([]);
const calendarRef = ref(null);
const uploadedFilde = ref(null);
const uploadedFile = ref(null);
const applyModalOpen = ref(false);
const organizations = ref({});
const careerSearch = ref("");
const careerDropdownOpen = ref(false);
const showConflictDialog = ref(false);
const conflictTraining = ref(null);
const conflictingTrainings = ref([]);
const pendingRegistration = ref(null);
const loadingPosts = ref(false);
const organizationsChoiceTrainings = ref([]); // Store org's choice trainings for selected career



const filteredCareers = computed(() => {
  const query = careerSearch.value.trim().toLowerCase();
  if (!query) {
    return allCareers.value;
  }
  return allCareers.value.filter((career) =>
    career.position?.toLowerCase().includes(query)
  );
});


const selectedTrainingRegistered = computed(() =>
  isTrainingRegistered(selectedTraining.value)
);

// Check if user has attended the selected training
const selectedTrainingAttended = computed(() =>
  hasUserAttended(selectedTraining.value)
);

// Check if selected training has ended
const selectedTrainingEnded = computed(() =>
  isTrainingEnded(selectedTraining.value)
);

// Get button text based on training state
const registrationButtonText = computed(() => {
  if (!selectedTraining.value) return "Register";
  
  // If user attended, show "Already Attended"
  if (selectedTrainingAttended.value) {
    return "Already Attended";
  }
  
  // If training ended and user didn't register/attend, show "Training has already ended"
  if (selectedTrainingEnded.value && !selectedTrainingRegistered.value) {
    return "Training has already ended";
  }
  
  // Otherwise, show Register/Unregister based on registration status
  return selectedTrainingRegistered.value ? "Unregister" : "Register";
});

// Check if button should be disabled
const isRegistrationButtonDisabled = computed(() => {
  if (trainingActionLoading.value) return true;
  
  if (!selectedTraining.value) return false;
  
  // Disable if user attended
  if (selectedTrainingAttended.value) {
    return true;
  }
  
  // Disable if training ended and user didn't register
  if (selectedTrainingEnded.value && !selectedTrainingRegistered.value) {
    return true;
  }
  
  return false;
});

// Calculate percentage of organizationschoice trainings attended
const organizationsChoiceAttendancePercentage = computed(() => {
  if (!selectedCareerId.value) {
    return null;
  }

  const totalOrgChoiceTrainings = organizationsChoiceTrainings.value.length;
  
  // If no org's choice trainings exist for this career, return 0%
  if (totalOrgChoiceTrainings === 0) {
    return 0;
  }

  // Count how many org's choice trainings the user has actually attended
  // Check hasAttended flag which is based on actual schedule_attendance records
  const attendedCount = organizationsChoiceTrainings.value.filter((training) => {
    const trainingID = training.trainingID || training.TrainingID;
    if (!trainingID) {
      console.warn('Training missing ID:', training);
      return false;
    }
    
    // Normalize training ID to number for comparison
    const normalizedTrainingID = Number(trainingID);
    
    // Find the registration for this training
    const registration = myRegistrationsData.value.find((reg) => {
      const regTrainingID = Number(reg.trainingID);
      return regTrainingID === normalizedTrainingID;
    });
    
    if (registration) {
      // Debug logging
      console.log('Found registration for training:', {
        trainingID: normalizedTrainingID,
        registrationID: registration.registrationID,
        hasAttended: registration.hasAttended,
        registrationStatus: registration.registrationStatus,
        attendanceCount: registration.attendanceCount
      });
      
      // Use hasAttended flag if available (from backend), otherwise fallback to registrationStatus
      if (registration.hasAttended !== undefined) {
        return registration.hasAttended === true;
      }
      // Fallback: check registrationStatus for backward compatibility
      const status = (registration.registrationStatus || '').toLowerCase().trim();
      return status === 'attended';
    } else {
      console.log('No registration found for training:', normalizedTrainingID);
    }
    
    return false;
  }).length;

  // Debug logging
  console.log('Attendance calculation:', {
    totalOrgChoiceTrainings,
    attendedCount,
    percentage: Math.round((attendedCount / totalOrgChoiceTrainings) * 100),
    orgChoiceTrainings: organizationsChoiceTrainings.value.map(t => ({
      trainingID: t.trainingID || t.TrainingID,
      title: t.title
    })),
    myRegistrations: myRegistrationsData.value.map(r => ({
      trainingID: r.trainingID,
      hasAttended: r.hasAttended,
      status: r.registrationStatus
    }))
  });

  // Calculate percentage (can be 0% to 100%)
  const percentage = Math.round((attendedCount / totalOrgChoiceTrainings) * 100);
  return percentage;
});

// Computed property for attendance count display
const organizationsChoiceAttendanceCount = computed(() => {
  if (!selectedCareerId.value) {
    return { attended: 0, total: 0 };
  }

  const total = organizationsChoiceTrainings.value.length;
  // Count how many org's choice trainings the user has actually attended
  // Check hasAttended flag which is based on actual schedule_attendance records
  const attended = organizationsChoiceTrainings.value.filter((training) => {
    const trainingID = training.trainingID || training.TrainingID;
    if (!trainingID) return false;
    
    // Find the registration for this training
    const registration = myRegistrationsData.value.find(
      (reg) => reg.trainingID === Number(trainingID) || reg.trainingID === trainingID
    );
    
    // Check if user has actually attended (has at least one schedule_attendance record)
    // This is more reliable than checking registrationStatus
    if (registration) {
      // Use hasAttended flag if available (from backend), otherwise fallback to registrationStatus
      if (registration.hasAttended !== undefined) {
        return registration.hasAttended === true;
      }
      // Fallback: check registrationStatus for backward compatibility
      const status = (registration.registrationStatus || '').toLowerCase().trim();
      return status === 'attended';
    }
    
    return false;
  }).length;

  return { attended, total };
});

// Build events only when posts are loaded
function buildEvents() {
  events.value = {};
  posts.value.forEach((post) => {
    const date = post.deadlineOfSubmission || (post.schedule ? post.schedule.split("T")[0] : null);
    if (date) {
      if (!events.value[date]) events.value[date] = [];
      events.value[date].push(post);
    }
  });
}

// ------------------ TOAST ------------------
function addToast(message, type = "info") {
  const id = Date.now();
  toasts.value.push({ id, message, type });
  setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
  }, 3000);
}

// ------------------ FETCH DATA ------------------
async function fetchAllCareers() {
  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/careers");
    allCareers.value = Array.isArray(res.data) ? res.data : [];
  } catch (err) {
    console.error(err);
    addToast("Failed to fetch careers", "error");
  }
}

async function fetchMyApplications() {
  try {
    const token = localStorage.getItem("token");
    if (!token) return;

    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/applications",
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    myApplications.value = new Set(res.data.map((a) => a.careerID));
    res.data.forEach((a) => (appliedPosts.value[a.careerID] = true));
  } catch (err) {
    console.error(err);
  }
}

async function fetchMyRegistrations() {
  try {
    const token = localStorage.getItem("token");
    if (!token) return;

    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/registrations",
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    myRegistrationsData.value = res.data || []; // Store full registration data
    myRegistrations.value = new Set(res.data.map((r) => r.trainingID));
    res.data.forEach((r) => (registeredPosts[r.trainingID] = true));
  } catch (err) {
    console.error(err);
  }
}



// ------------------ MODALS ------------------
async function openCareerModal(career) {
  try {
    // Validate career object
    if (!career) {
      addToast("Invalid career data.", "error");
      return;
    }

    // Validate and parse careerID
    const careerID = career.careerID;
    if (careerID === undefined || careerID === null) {
      addToast("Career ID is missing.", "error");
      return;
    }

    const parsedCareerID = parseInt(careerID, 10);
    if (isNaN(parsedCareerID)) {
      addToast("Invalid career ID format.", "error");
      return;
    }

    console.log("Career Object:", career);
    console.log("Parsed Career ID:", parsedCareerID);

    // Make API request
    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + `/careers/${parsedCareerID}/details`
    );

    if (!res.data || !res.data.career) {
      addToast("Career details not found.", "error");
      return;
    }

    // Store main career
    selectedCareerDetails.value = normalizeCareerDetails(res.data.career);

    if (!selectedCareerDetails.value) {
      addToast("Career details not found.", "error");
      return;
    }

    // Normalize career closing date
    const careerClosingDate = selectedCareerDetails.value.closingDate
      ? new Date(selectedCareerDetails.value.closingDate)
      : null;

    if (careerClosingDate) {
      careerClosingDate.setHours(0, 0, 0, 0);
    }

    const rawTrainings = res.data.recommended_trainings || [];
    console.log("API Response - recommended_trainings:", rawTrainings);

    // 🔥 FINAL FILTER (schedule-based, correct)
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const filteredTrainings = careerClosingDate
      ? rawTrainings.filter(training => {
          const schedules = Array.isArray(training.schedules) && training.schedules.length
            ? training.schedules
            : training.schedule
              ? [{ schedule: training.schedule }]
              : [];

          if (!schedules.length) return false;

          return schedules.some(schedule => {
            const raw =
              schedule.schedule ||
              schedule.Schedule ||
              schedule.date;

            if (!raw) return false;

            const datePart = raw.includes("T")
              ? raw.split("T")[0]
              : raw.split(" ")[0];

            const scheduleDate = new Date(datePart);
            scheduleDate.setHours(0, 0, 0, 0);

            // ✅ ONLY allow: today → career closing date
            return (
              scheduleDate >= today &&
              scheduleDate <= careerClosingDate
            );
          });
        })
      : rawTrainings;

    console.log("Career closing date:", careerClosingDate);
    console.log("Filtered trainings:", filteredTrainings);

    // Aggregate AFTER filtering
    recommendedTrainings.value =
      aggregateRecommendedTrainings(filteredTrainings);

    console.log("After aggregation:", recommendedTrainings.value);

    showCareerPopup.value = true;
  } catch (error) {
    console.error("Error loading career details:", error);

    if (error.response) {
      const status = error.response.status;
      if (status === 404) {
        addToast("Career not found.", "error");
      } else if (status === 500) {
        addToast("Server error. Please try again later.", "error");
      } else {
        addToast(`Failed to load career details (${status}).`, "error");
      }
    } else if (error.request) {
      addToast("Network error. Please check your connection.", "error");
    } else {
      addToast("Failed to load career details.", "error");
    }
  }
}

async function handleCardClick(career, index) {
  const uniqueId = career.careerID + '-' + index;
  loadingCardId.value = uniqueId;

  try {
    await openCareerModal(career);
  } finally {
    loadingCardId.value = null;
  }
}

function closeCareerModal() {
  showCareerPopup.value = false;
  selectedCareerDetails.value = null;
  recommendedTrainings.value = [];
}

// Open training modal
function openTrainingModal(training) {
  selectedTraining.value = training;
  trainingActionLoading.value = false;
  trainingActionError.value = "";
  showTrainingModal.value = true;
}

// Close training modal
function closeTrainingModal() {
  showTrainingModal.value = false;
  selectedTraining.value = null;
  trainingActionLoading.value = false;
  trainingActionError.value = "";
}

// Cancel application
function cancelApplication(career) {
  const id = career.careerID;
  appliedPosts.value[id] = false;
  myApplications.value.delete(id);
}

// Check for same-day onsite conflicts
async function checkSameDayOnsiteConflict(newTraining) {
  const token = localStorage.getItem("token");
  if (!token) return false;

  try {
    // Fetch existing registrations
    const registrationsRes = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/registrations",
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    const existingRegistrations = registrationsRes.data || [];

    // Get schedules from the new training
    const newTrainingSchedules = newTraining?.schedules || [];
    if (!Array.isArray(newTrainingSchedules) && newTraining?.schedule) {
      // Handle single schedule format
      newTrainingSchedules.push({
        schedule: newTraining.schedule,
        mode: newTraining.mode,
        location: newTraining.location,
      });
    }

    // Check if new training has any onsite schedules
    const newTrainingHasOnsite = newTrainingSchedules.some((schedule) => {
      const mode = getScheduleMode(schedule);
      return isOnsiteMode(mode);
    });

    if (!newTrainingHasOnsite) {
      return false; // No conflict if new training is not onsite
    }

    // Extract dates from new training schedules
    const newTrainingDates = new Set();
    newTrainingSchedules.forEach((schedule) => {
      const scheduleDate = schedule?.schedule || schedule?.start_time;
      if (scheduleDate) {
        const date = new Date(scheduleDate);
        const dateStr = date.toISOString().split("T")[0]; // YYYY-MM-DD
        newTrainingDates.add(dateStr);
      }
    });

    // Check for conflicts with existing registrations
    const conflicts = [];
    existingRegistrations.forEach((registration) => {
      const existingTraining = registration?.training;
      if (!existingTraining) return;

      const existingSchedules = existingTraining?.schedules || [];
      if (!Array.isArray(existingSchedules) && existingTraining?.schedule) {
        existingSchedules.push({
          schedule: existingTraining.schedule,
          mode: existingTraining.mode,
          location: existingTraining.location,
        });
      }

      existingSchedules.forEach((existingSchedule) => {
        const existingMode = getScheduleMode(existingSchedule);
        if (!isOnsiteMode(existingMode)) return; // Only check onsite trainings

        const existingScheduleDate = existingSchedule?.schedule || existingSchedule?.start_time;
        if (existingScheduleDate) {
          const existingDate = new Date(existingScheduleDate);
          const existingDateStr = existingDate.toISOString().split("T")[0];

          // Check if same day
          if (newTrainingDates.has(existingDateStr)) {
            conflicts.push({
              training: existingTraining,
              schedule: existingSchedule,
              date: existingDateStr,
            });
          }
        }
      });
    });

    return conflicts.length > 0 ? conflicts : false;
  } catch (error) {
    console.error("Error checking conflicts:", error);
    return false; // On error, allow registration to proceed
  }
}

// Register for training
async function registerForTraining(training, skipConflictCheck = false) {
  if (!training) return;

  const trainingId = resolveTrainingId(training);
  if (trainingId === null || trainingId === undefined) {
    addToast("Invalid training ID", "accent");
    return;
  }

  const affectsSelected =
    selectedTraining.value &&
    resolveTrainingId(selectedTraining.value) === trainingId;

  // Check for same-day onsite conflicts before registering
  if (!skipConflictCheck) {
    const conflicts = await checkSameDayOnsiteConflict(training);
    if (conflicts && conflicts.length > 0) {
      conflictTraining.value = training;
      conflictingTrainings.value = conflicts;
      pendingRegistration.value = { training, affectsSelected };
      showConflictDialog.value = true;
      return;
    }
  }

  // Proceed with registration
  await proceedWithRegistration(training, affectsSelected);
}

// Proceed with actual registration
async function proceedWithRegistration(training, affectsSelected) {
  const trainingId = resolveTrainingId(training);
  
  try {
    const token = localStorage.getItem("token");
    if (!token) {
      addToast("PLEASE LOG IN FIRST", "accent");
      return;
    }

    if (affectsSelected) {
      trainingActionLoading.value = true;
      trainingActionError.value = "";
    }

    await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/registrations",
      { trainingID: trainingId },
      { headers: { Authorization: `Bearer ${token}` } }
    );

    addToast("REGISTRATION SUCCESSFUL!!!", "success");
    myRegistrations.value.add(Number(trainingId));
    registeredPosts[trainingId] = true;
  } catch (error) {
    if (affectsSelected) {
      trainingActionError.value =
        error.response?.data?.message ||
        error.response?.data?.error ||
        "Failed to register. Please try again.";
    }
    if (error.response?.status === 409) {
      addToast("YOU ALREADY REGISTERED FOR THIS TRAINING", "accent");
    } else if (error.response?.status === 401) {
      addToast("UNAUTHORIZED. PLEASE LOG IN AGAIN", "accent");
    } else {
      addToast("FAILED TO REGISTER", "accent");
    }
  } finally {
    if (affectsSelected) {
      trainingActionLoading.value = false;
    }
  }
}

// Handle conflict dialog confirmation
async function confirmConflictRegistration() {
  showConflictDialog.value = false;
  if (pendingRegistration.value) {
    const { training, affectsSelected } = pendingRegistration.value;
    pendingRegistration.value = null;
    await proceedWithRegistration(training, affectsSelected);
  }
  conflictTraining.value = null;
  conflictingTrainings.value = [];
}

// Handle conflict dialog cancellation
function cancelConflictRegistration() {
  showConflictDialog.value = false;
  pendingRegistration.value = null;
  conflictTraining.value = null;
  conflictingTrainings.value = [];
}

// Unregister from training
async function unregisterFromTraining(training) {
  if (!training) return;

  const trainingId = resolveTrainingId(training);
  if (trainingId === null || trainingId === undefined) {
    addToast("Invalid training ID", "accent");
    return;
  }

  const affectsSelected =
    selectedTraining.value &&
    resolveTrainingId(selectedTraining.value) === trainingId;

  try {
    const token = localStorage.getItem("token");
    if (!token) return;

    if (affectsSelected) {
      trainingActionLoading.value = true;
      trainingActionError.value = "";
    }

    // Find registration ID
    const registrationsRes = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/registrations",
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    const registration = registrationsRes.data.find(
      (r) => Number(r.trainingID) === Number(trainingId)
    );
    if (registration) {
      await axios.delete(
        import.meta.env.VITE_API_BASE_URL + `/registrations/${registration.id}`,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
      myRegistrations.value.delete(Number(trainingId));
      registeredPosts[trainingId] = false;
      addToast("Unregistered successfully", "info");
    }
  } catch (error) {
    if (affectsSelected) {
      trainingActionError.value =
        error.response?.data?.message ||
        error.response?.data?.error ||
        "Failed to unregister. Please try again.";
    }
    console.error("Error unregistering:", error);
    addToast("Failed to unregister", "error");
  } finally {
    if (affectsSelected) {
      trainingActionLoading.value = false;
    }
  }
}

async function submitApplication() {
  if (!selectedPost.value) return;

  const token = localStorage.getItem("token");
  if (!token) {
    addToast("PLEASE LOG IN FIRST", "accent");
    return;
  }

  if (!uploadedFile.value) {
    addToast("PLEASE ATTACH YOUR REQUIREMENTS PDF", "accent");
    return;
  }

  if (uploadedFile.value.size > 5 * 1024 * 1024) {
    addToast("PDF SIZE EXCEEDS 5MB LIMIT. PLEASE UPLOAD A SMALLER FILE", "accent");
    return;
  }

  if (uploadedFile.value.type !== "application/pdf") {
    addToast("PLEASE UPLOAD A PDF FILE", "accent");
    return;
  }

  isSubmitting.value = true; // start loading

  try {
    const formData = new FormData();
    formData.append("careerID", selectedPost.value.careerID ?? selectedPost.value.id);
    formData.append("requirement_directory", uploadedFile.value);

    await axios.post(`${API_BASE_URL}/applications`, formData, {
      headers: {
        Authorization: `Bearer ${token}`,
        "Content-Type": "multipart/form-data",
      },
    });

    addToast("APPLICATION SUBMITTED SUCCESSFULLY", "success");
    myApplications.value.add(selectedPost.value.careerID ?? selectedPost.value.id);

    closeApplyModal();
  } catch (error) {
    if (error.response?.status === 409) {
      addToast("YOU ALREADY APPLIED TO THIS CAREER", "accent");
    } else if (error.response?.status === 401) {
      addToast("UNAUTHORIZED. PLEASE LOG IN AGAIN", "accent");
    } else {
      addToast(error.response?.data?.message || "FAILED TO SUBMIT APPLICATION", "accent");
      console.error("Application submission error:", error.response?.data || error);
    }
  } finally {
    isSubmitting.value = false; // stop loading
  }
}

async function unapplyApplication() {
  if (!props.career) return;
  const token = localStorage.getItem("token");
  if (!token) {
    addToast("PLEASE LOG IN FIRST", "accent");
    return;
  }

  const careerId = getCareerId();
  if (!careerId) {
    addToast("INVALID CAREER DATA", "accent");
    return;
  }

  if (unapplyLoading.value) return;
  unapplyLoading.value = true;

  try {
    await axios.delete(
      `${API_BASE_URL}/applications/career/${careerId}`,
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    addToast("APPLICATION WITHDRAWN", "success");
    const updatedSet = new Set(props.myApplications ?? []);
    updatedSet.delete(careerId);
    emits("update-applications", updatedSet);
    emits("close");
  } catch (error) {
    const errorMsg =
      error.response?.data?.message || "FAILED TO WITHDRAW APPLICATION";
    addToast(errorMsg, "accent");
    console.error("Application withdrawal error:", error.response?.data || error);
  } finally {
    unapplyLoading.value = false;
  }
}

function openModal(post) {
  selectedPost.value = post;
}

function closeModal() {
  selectedPost.value = null;
}

// Show events on calendar click
function showEvents(dateStr) {
  selectedDate.value = dateStr;
  dayEvents.value = events.value[dateStr] || [];
}





function openApplyModal(career) {
  selectedPost.value = career;
  applyModalOpen.value = true;
}

function closeApplyModal() {
  applyModalOpen.value = false;
  uploadedFile.value = null;
  selectedPost.value = null;
}

function handleFileUpload(e) {
  const file = e.target.files[0];
  if (!file) return;

  // Check if file is PDF
  if (file.type !== "application/pdf") {
    addToast("PLEASE UPLOAD A PDF FILE", "error");
    e.target.value = "";
    return;
  }

  // Check file size (5MB = 5 * 1024 * 1024 bytes)
  const MAX_SIZE = 5 * 1024 * 1024; // 5MB in bytes
  if (file.size > MAX_SIZE) {
    addToast("PDF SIZE EXCEEDS 5MB LIMIT. PLEASE UPLOAD A SMALLER FILE", "error");
    e.target.value = "";
    uploadedFile.value = null;
    return;
  }

  uploadedFile.value = file;
}

async function fetchRecommendedCareers() {
  if (!selectedCareerId.value) {
    posts.value = [];
    return;
  }

  loadingPosts.value = true; // start loading
  try {
    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL +
      `/careers/recommend/${selectedCareerId.value}`
    );

    // API SHOULD RETURN LIST OF CAREERS
    posts.value = Array.isArray(res.data) ? res.data : [];

  } catch (err) {
    console.error("Error fetching recommended careers:", err);
    posts.value = [];
    addToast("Failed to load recommended careers", "error");
  } finally {
    loadingPosts.value = false; // stop loading
  }
}

function openCareerDropdown() {
  careerDropdownOpen.value = true;
  handleCareerInput(); // optional: fetch/filter immediately on focus
}

function closeCareerDropdown() {
  setTimeout(() => { // small delay to allow click selection
    careerDropdownOpen.value = false;
  }, 100);
}

function handleCareerInput() {
  loadingCareers.value = true;

  // Simulate API call or filtering delay
  setTimeout(() => {
    filteredCareers.value = allCareers.value.filter(career =>
      career.position.toLowerCase().includes(careerSearch.value.toLowerCase())
    );
    loadingCareers.value = false;
  }, 500); // 500ms delay to simulate loading
}

function selectCareer(career) {
  selectedCareerId.value = career.careerID;
  careerSearch.value = career.position;
  careerDropdownOpen.value = false;
  fetchRecommendedCareers();
  fetchOrganizationsChoiceTrainings();
}

async function fetchOrganizationsChoiceTrainings() {
  if (!selectedCareerId.value) {
    organizationsChoiceTrainings.value = [];
    return;
  }

  try {
    console.log("Fetching organizationschoice trainings for career:", selectedCareerId.value);
    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + `/careers/${selectedCareerId.value}/organizations-choice-trainings`
    );
    organizationsChoiceTrainings.value = Array.isArray(res.data) ? res.data : [];
    console.log("Organizationschoice trainings loaded:", organizationsChoiceTrainings.value.length, organizationsChoiceTrainings.value);
    
    // Refresh registration data to ensure we have latest attendance info
    await fetchMyRegistrations();
  } catch (err) {
    console.error("Error fetching organizationschoice trainings:", err);
    organizationsChoiceTrainings.value = [];
  }
}

function normalizeCareerDetails(career) {
  if (!career) return null;
  return {
    ...career,
    organization:
      career.organization ||
      career.organizationName ||
      career.organization_name ||
      "Unknown Organization",
    details:
      career.details ||
      career.detailsAndInstructions ||
      career.description ||
      "",
    qualificationStandard:
      career.qualificationStandard ||
      career.qualifications ||
      career.qualification ||
      "",
    placeOfAssignment:
      career.placeOfAssignment ||
      career.location ||
      career.place ||
      "",
    closingDate:
      career.closingDate || career.deadlineOfSubmission || career.dueDate || "",
    postingDate: career.postingDate || career.openingDate || "",
  };
}

function viewCareerPDF() {
  const filePath = selectedCareerDetails.value?.pdf_directory;
  if (!filePath) {
    addToast("PDF not available", "accent");
    return;
  }

  try {
    const pdfUrl = getPDFUrl(filePath, "Requirements");
    if (pdfUrl) {
      window.open(pdfUrl, "_blank");
      return;
    }

    if (filePath.startsWith("http://") || filePath.startsWith("https://")) {
      window.open(filePath, "_blank");
      return;
    }

    addToast("Failed to open PDF", "accent");
  } catch (error) {
    console.error("Error opening PDF:", error);
    addToast("Failed to open PDF", "accent");
  }
}

function resolveTrainingId(training) {
  if (!training) return null;
  const id =
    training.trainingID ??
    training.TrainingID ??
    training.id ??
    training.ID ??
    null;
  if (id === null || id === undefined) return null;
  return typeof id === "string" ? Number(id) || id : id;
}

function isTrainingRegistered(training) {
  const id = resolveTrainingId(training);
  if (id === null || id === undefined) return false;
  return (
    myRegistrations.value.has(Number(id)) ||
    myRegistrations.value.has(id)
  );
}

// Parse date string to local Date object (prevents timezone issues)
function parseLocalDateTime(dateString) {
  if (!dateString) return null;
  
  try {
    // Split by space or T to get date and time parts
    const [datePart, timePart] = dateString.split(/[ T]/);
    if (!datePart) return null;
    
    const [year, month, day] = datePart.split("-");
    const timeStr = timePart || "00:00:00";
    const [hour, minute, second] = timeStr.split(":");
    
    // Create date in local timezone (not UTC)
    return new Date(
      Number(year),
      Number(month) - 1, // Month is 0-indexed
      Number(day),
      Number(hour || 0),
      Number(minute || 0),
      Number(second || 0)
    );
  } catch (error) {
    console.error("Error parsing date:", dateString, error);
    return null;
  }
}

// Check if training has ended
function isTrainingEnded(training) {
  if (!training) return false;
  
  const now = new Date();
  
  // Check if training has multiple schedules
  if (training.schedules && Array.isArray(training.schedules) && training.schedules.length > 0) {
    // Check if all schedules have ended (no future schedules, and latest end time has passed)
    let latestEndTime = null;
    let hasFutureSchedule = false;
    
    for (const schedule of training.schedules) {
      // Check if this schedule hasn't started yet (future schedule)
      const scheduleTime = schedule.schedule || schedule.Schedule;
      if (scheduleTime) {
        const startTime = parseLocalDateTime(scheduleTime);
        if (startTime && startTime.getTime() > now.getTime()) {
          hasFutureSchedule = true;
          break; // Found a future schedule, so training is not ended
        }
      }
      
      // Track latest end time
      const endTime = schedule.end_time || schedule.endTime || schedule.endTimeDate;
      if (endTime) {
        const endTimeDate = parseLocalDateTime(endTime);
        if (endTimeDate && (!latestEndTime || endTimeDate.getTime() > latestEndTime.getTime())) {
          latestEndTime = endTimeDate;
        }
      }
    }
    
    // If there's a future schedule, it's not ended
    if (hasFutureSchedule) {
      return false;
    }
    
    // No future schedules, check if latest end time has passed
    if (latestEndTime) {
      return latestEndTime.getTime() <= now.getTime();
    }
    
    return false;
  }
  
  // Single schedule format (backward compatibility)
  const scheduleTime = training.schedule || training.Schedule;
  const endTime = training.end_time || training.endTime || training.endTimeDate;
  
  if (!scheduleTime) {
    return false;
  }
  
  const startTime = parseLocalDateTime(scheduleTime);
  
  // If schedule hasn't started yet, it's not ended
  if (startTime && startTime.getTime() > now.getTime()) {
    return false;
  }
  
  // Schedule has started, check if it has ended
  if (endTime) {
    const endTimeDate = parseLocalDateTime(endTime);
    if (endTimeDate) {
      return endTimeDate.getTime() <= now.getTime();
    }
  }
  
  return false;
}

// Check if user has attended the training
function hasUserAttended(training) {
  if (!training) return false;
  
  const trainingID = resolveTrainingId(training);
  if (trainingID === null || trainingID === undefined) return false;
  
  // Find the registration for this training
  const registration = myRegistrationsData.value.find((reg) => {
    const regTrainingID = Number(reg.trainingID);
    const normalizedTrainingID = Number(trainingID);
    return regTrainingID === normalizedTrainingID;
  });
  
  if (registration) {
    // Use hasAttended flag if available (from backend), otherwise fallback to registrationStatus
    if (registration.hasAttended !== undefined) {
      return registration.hasAttended === true;
    }
    // Fallback: check registrationStatus for backward compatibility
    const status = (registration.registrationStatus || '').toLowerCase().trim();
    return status === 'attended';
  }
  
  return false;
}


function formatScheduleFull(schedule) {
  if (!schedule) return "No schedule set";
  try {
    const [datePart, timePart] = schedule.split(/[ T]/);
    const [year, month, day] = datePart.split("-");
    const [hour = "00", minute = "00"] = (timePart || "").split(":");
    const date = new Date(
      Number(year),
      Number(month) - 1,
      Number(day),
      Number(hour),
      Number(minute)
    );
    return date.toLocaleString("en-US", {
      year: "numeric",
      month: "long",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
      hour12: true,
    });
  } catch (error) {
    return schedule || "Invalid date";
  }
}

function formatScheduleTime(schedule) {
  if (!schedule) return "";
  try {
    const [datePart, timePart] = schedule.split(/[ T]/);
    const [year, month, day] = datePart.split("-");
    const [hour = "00", minute = "00"] = (timePart || "").split(":");
    const date = new Date(
      Number(year),
      Number(month) - 1,
      Number(day),
      Number(hour),
      Number(minute)
    );

    return date.toLocaleString("en-US", {
      hour: "2-digit",
      minute: "2-digit",
      hour12: true,
    });
  } catch (error) {
    return "";
  }
}

function getScheduleMode(schedule) {
  return (schedule?.mode || schedule?.Mode || "Not specified").toString().trim();
}

function isOnsiteMode(mode) {
  const value = (mode || "").toString().toLowerCase();
  return value === "on-site" || value === "onsite";
}

function isOnlineMode(mode) {
  const value = (mode || "").toString().toLowerCase();
  return value === "online";
}

function aggregateRecommendedTrainings(trainings) {
  if (!Array.isArray(trainings)) return [];

  const map = new Map();

  trainings.forEach((item) => {
    if (!item) return;

    const id =
      resolveTrainingId(item) ??
      item.trainingTitle ??
      item.title ??
      item.name ??
      `temp-${map.size}`;

    let entry = map.get(id);
    if (!entry) {
      entry = {
        ...item,
        trainingID: resolveTrainingId(item) ?? id,
        title: item.title || item.trainingTitle || item.name || "Untitled Training",
        description: item.description || "",
        organizationName:
          item.organizationName ||
          item.organization ||
          item.provider ||
          "",
        provider: item.provider || item.organizationName || item.organization || "",
        schedules: Array.isArray(item.schedules) ? [...item.schedules] : [],
        isOrganizationsChoice: item.isOrganizationsChoice || false,
      };
      map.set(id, entry);
    } else {
      entry.description = entry.description || item.description || "";
      entry.organizationName =
        entry.organizationName ||
        item.organizationName ||
        item.organization ||
        item.provider ||
        "";
      entry.provider = entry.provider || item.provider;
      // Preserve isOrganizationsChoice flag if any item has it set to true
      entry.isOrganizationsChoice = entry.isOrganizationsChoice || item.isOrganizationsChoice || false;
      if (Array.isArray(item.schedules)) {
        item.schedules.forEach((sched) => {
          if (!entry.schedules.some((existing) => existing.trainingScheduleID === sched.trainingScheduleID)) {
            entry.schedules.push(sched);
          }
        });
      }
    }

    const schedule = extractScheduleFromTraining(item);
    if (schedule) {
      const duplicate = entry.schedules.some((existing) => {
        if (schedule.trainingScheduleID && existing.trainingScheduleID) {
          return existing.trainingScheduleID === schedule.trainingScheduleID;
        }
        return (
          existing.schedule === schedule.schedule &&
          existing.mode === schedule.mode &&
          existing.location === schedule.location
        );
      });
      if (!duplicate) {
        entry.schedules.push(schedule);
      }
    }
  });

  return Array.from(map.values());
}

function extractScheduleFromTraining(training) {
  const schedule =
    training.schedule ||
    training.Schedule ||
    training.start_time ||
    training.startTime ||
    null;

  const hasDetails =
    schedule ||
    training.end_time ||
    training.endTime ||
    training.mode ||
    training.Mode ||
    training.location ||
    training.trainingLink;

  if (!hasDetails) {
    return null;
  }

  return {
    trainingScheduleID:
      training.trainingScheduleID ||
      training.scheduleID ||
      training.TrainingScheduleID ||
      null,
    schedule,
    end_time: training.end_time || training.endTime || null,
    mode: training.mode || training.Mode || "",
    location: training.location || training.place || training.venue || "",
    trainingLink: training.trainingLink || training.link || "",
  };
}
// ------------------ ACTIONS ------------------


// ------------------ HELPER ------------------
const isTraining = (post) => !!post.trainingID;
const isRegisteredOrApplied = (post) =>
  isTraining(post)
    ? myRegistrations.value.has(post.trainingID)
    : myApplications.value.has(post.careerID);

function formatDateTime(dt) {
  if (!dt) return "";
  return new Date(dt).toLocaleString("en-US", {
    dateStyle: "long",
    timeStyle: "short",
  });
}



function formatDate(d) {
  if (!d) return "";
  return new Date(d).toLocaleDateString("en-US", { dateStyle: "long" });
}

// ------------------ LIFECYCLE ------------------
onMounted(async () => {
  await fetchAllCareers();
  await fetchMyApplications();
  await fetchMyRegistrations();
});

// Watch for changes in selected career to fetch organizationschoice trainings
watch(selectedCareerId, (newCareerId) => {
  if (newCareerId) {
    fetchOrganizationsChoiceTrainings();
  } else {
    organizationsChoiceTrainings.value = [];
  }
});
</script>

<template>
  <div class="font-poppins">
    <!-- Layout wrapper -->
    <div class="relative font-poppins min-h-screen flex">
      <!-- MAIN CONTENT -->
      <main class="flex-1 bg-white m-3 px-4 rounded-lg flex flex-col min-h-0 overflow-hidden">
        <!-- Sticky Header -->
        <div class="sticky top-0 z-10 bg-white pt-4 px-4 pb-2 border-b shadow-sm relative">
          <div class="flex items-start justify-between gap-4 mb-2">
            <div>
              <h2 class="text-lg font-bold">Career-Training</h2>
              <h2 class="text-2xl font-bold">Matching Engine</h2>
            </div>
            <!-- OrganizationsChoice Attendance Percentage -->
            <div
              v-if="selectedCareerId && organizationsChoiceAttendancePercentage !== null"
              class="flex-shrink-0 px-3 sm:px-4 py-3 sm:py-4 bg-gradient-to-r from-yellow-400 to-yellow-500 text-yellow-900 rounded-lg shadow-lg border-2 border-yellow-600 z-50 flex flex-col items-center justify-center w-[100px] h-[100px] sm:w-[180px] sm:h-[100px]"
            >
              <div class="text-[9px] sm:text-[10px] font-semibold mb-1 uppercase tracking-wide text-center">Career Progress</div>
              <div class="text-2xl sm:text-3xl font-bold">{{ organizationsChoiceAttendancePercentage }}%</div>
            </div>
            <!-- Message when no target career is selected -->
            <div
              v-else-if="!selectedCareerId"
              class="flex-shrink-0 px-3 sm:px-4 py-3 sm:py-4 bg-gray-100 text-gray-600 rounded-lg shadow-md border border-gray-300 z-50 flex flex-col items-center justify-center w-[100px] h-[100px] sm:w-[200px] sm:h-[100px]"
            >
              <div class="text-[9px] sm:text-[10px] font-semibold mb-1 text-gray-700 text-center">Target Career</div>
              <div class="text-xs sm:text-sm leading-tight text-center">
                Select a target career to see progress
              </div>
            </div>
          </div>

          <div class="mt-4 mb-4">
          <label for="career-select" class="block text-sm font-medium text-gray-700 mb-2 texr-xs">
            Select Your Matched Career
          </label>
          <div class="relative">
            <input
              id="career-select"
              type="text"
              v-model="careerSearch"
              @focus="openCareerDropdown"
              @input="handleCareerInput"
              @blur="closeCareerDropdown"
              placeholder="Type to search careers"
              class="block w-full px-4 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md bg-gray-300"
              autocomplete="off"
            />
              <div
                  v-if="careerDropdownOpen"
                  class="absolute mt-1 w-full max-h-60 overflow-auto bg-white border border-gray-200 rounded-md shadow-lg z-50"
                >
              <!-- Loading state -->
              <div v-if="loadingCareers" class="flex justify-center items-center p-4">
                <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4l-3 3 3 3h-4z"></path>
                </svg>
                <span class="ml-2 text-gray-500 text-sm">Loading...</span>
              </div>

              <!-- Career options -->
              <template v-else>
                <button
                  v-for="career in filteredCareers"
                  :key="career.careerID"
                  type="button"
                  class="w-full text-left px-4 py-2 text-sm hover:bg-indigo-50"
                  @mousedown.prevent="selectCareer(career)"
                >
                  <span class="font-medium">{{ career.position }}</span>
                  <p class="text-xs text-gray-500">
                    {{ career.organization || "Unknown Organization" }}
                  </p>
                </button>

                <p v-if="filteredCareers.length === 0" class="px-4 py-2 text-sm text-gray-500">
                  No careers found.
                </p>
              </template>
            </div>
          </div>
        </div>
        </div>

        <!-- Scrollable Posts -->
        <div
            class="flex-1 overflow-y-auto space-y-4 pb-4 pt-4 relative z-0"
          >
          <!-- Loading State -->
          <div v-if="loadingPosts" class="flex justify-center items-center py-8">
            <svg
            class="animate-spin h-10 w-10 text-blue-600"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            ></circle>
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8v8H4z"
            ></path>
          </svg>
            <span class="ml-2 text-gray-500 text-sm">Loading recommended careers...</span>
          </div>

          <!-- No posts messages -->
          <div v-else-if="posts.length === 0 && selectedCareerId" class="text-center text-gray-500 py-8">
            No recommended careers found.
          </div>
          <div v-else-if="posts.length === 0" class="text-center text-gray-500 py-8">
            Please select a target career to see recommendations.
          </div>

          <!-- Career posts -->
          <div
            v-else
            v-for="(post, index) in posts"
            :key="post.careerID + '-' + index"
            class="relative p-4 rounded-lg cursor-pointer transition"
            :class="[
              careerDropdownOpen ? 'bg-blue-gray' : 'bg-blue-gray hover:bg-gray-300',
              loadingCardId === (post.careerID + '-' + index) ? 'opacity-50' : ''
            ]"
            @click="handleCardClick(post, index)"
          >
            <div class="flex items-center justify-between z-10 relative">
              <div class="flex-1">
                <h3 class="font-semibold text-sm">{{ post.position }}</h3>
                <p class="text-gray-600 text-xs">{{ post.organization || 'Unknown Organization' }}</p>
              </div>
              <span v-if="post.careerID === selectedCareerId" class="ml-2 px-2 py-1 text-xs bg-blue-500 text-white rounded-full">
                Target
              </span>
            </div>

            <!-- Spinner overlay -->
            <div v-if="loadingCardId === (post.careerID + '-' + index)" class="absolute inset-0 bg-white bg-opacity-70 flex items-center justify-center rounded-lg z-0">
              <svg class="animate-spin h-6 w-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4l-3 3 3 3h-4z"></path>
              </svg>
            </div>
          </div>
        </div>
      </main>
    </div>

    <CalendarSidebar :isOpen="calendarOpen" @open="calendarOpen = true" @close="calendarOpen = false"
      @eventClick="openModalCalendar" />

    <!-- 🟦 Training Modal -->
    <div v-if="showTrainingModal && selectedTraining" class="training-modal-overlay" @click.self="closeTrainingModal">
      <div class="training-modal-box">
        <button class="training-modal-close" @click="closeTrainingModal">✕</button>

        <div class="flex-1 overflow-y-auto pr-4">
          <h2 class="training-modal-title">
            {{ selectedTraining.title || "Untitled Training" }}
          </h2>
          <p class="training-modal-info">
            <strong>Organization:</strong>
            {{
              selectedTraining.organization?.name ||
              selectedTraining.organizationName ||
              selectedTraining.provider ||
              selectedTraining.organization ||
              "Unknown"
            }}
          </p>
          <p class="training-modal-info">
            <strong>Description:</strong>
            {{ selectedTraining.description || "No description provided." }}
          </p>

          <div class="training-modal-section-title">Schedule/s</div>

          <div class="training-schedules-container">
          <template v-if="selectedTraining?.schedules?.length">
            <div class="training-schedules-grid">
              <div
                v-for="(schedule, index) in selectedTraining.schedules"
                :key="schedule.trainingScheduleID || index"
                class="training-schedule-card"
              >
                <div class="training-schedule-date">
                  <svg
                    class="training-schedule-icon"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                  <span>
                    {{ formatScheduleFull(schedule.schedule) }}
                    <template v-if="schedule.end_time">
                      - {{ formatScheduleTime(schedule.end_time) }}
                    </template>
                  </span>
                </div>

                <div
                  class="training-schedule-mode"
                  :class="
                    isOnsiteMode(getScheduleMode(schedule))
                      ? 'mode-onsite'
                      : 'mode-online'
                  "
                >
                  <svg
                    class="training-mode-icon"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                  </svg>
                  <span>{{ getScheduleMode(schedule) }}</span>
                </div>

                <div
                  v-if="isOnsiteMode(getScheduleMode(schedule)) && schedule.location"
                  class="training-schedule-extra"
                >
                  <svg
                    class="training-schedule-icon"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                  </svg>
                  <span>{{ schedule.location }}</span>
                </div>

                <div
                  v-if="isOnlineMode(getScheduleMode(schedule)) && schedule.trainingLink"
                  class="training-schedule-extra"
                >
                  <svg
                    class="training-schedule-icon"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                    />
                  </svg>
                  <a
                    :href="schedule.trainingLink"
                    target="_blank"
                    class="training-schedule-link"
                  >
                    {{ schedule.trainingLink }}
                  </a>
                </div>
              </div>
            </div>
          </template>

          <template v-else-if="selectedTraining.schedule">
            <div class="training-schedule-card">
              <div class="training-schedule-date">
                <svg
                  class="training-schedule-icon"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
                <span>
                  {{ formatScheduleFull(selectedTraining.schedule) }}
                  <template v-if="selectedTraining.end_time">
                    - {{ formatScheduleTime(selectedTraining.end_time) }}
                  </template>
                </span>
              </div>

              <div
                class="training-schedule-mode"
                :class="
                  isOnsiteMode(getScheduleMode(selectedTraining))
                    ? 'mode-onsite'
                    : 'mode-online'
                "
              >
                <svg
                  class="training-mode-icon"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                </svg>
                <span>{{ getScheduleMode(selectedTraining) }}</span>
              </div>

              <div
                v-if="isOnsiteMode(getScheduleMode(selectedTraining)) && selectedTraining.location"
                class="training-schedule-extra"
              >
                <svg
                  class="training-schedule-icon"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                </svg>
                <span>{{ selectedTraining.location }}</span>
              </div>

              <div
                v-if="isOnlineMode(getScheduleMode(selectedTraining)) && selectedTraining.trainingLink"
                class="training-schedule-extra"
              >
                <svg
                  class="training-schedule-icon"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                  />
                </svg>
                <a
                  :href="selectedTraining.trainingLink"
                  target="_blank"
                  class="training-schedule-link"
                >
                  {{ selectedTraining.trainingLink }}
                </a>
              </div>
            </div>
          </template>

          <div v-else class="training-schedule-card training-schedule-empty">
            No schedule set.
          </div>
          </div>
        </div>

        <!-- Button at bottom - full width -->
        <div class="mt-4 pt-4 border-t border-gray-300 flex flex-col gap-2">
          <button
            class="btn w-full text-white flex items-center justify-center gap-2"
            :class="
              isRegistrationButtonDisabled || selectedTrainingRegistered
                ? 'bg-gray-500 hover:bg-gray-600'
                : 'bg-customButton hover:bg-dark-slate'
            "
            @click="
              selectedTrainingRegistered && !isRegistrationButtonDisabled
                ? unregisterFromTraining(selectedTraining)
                : registerForTraining(selectedTraining)
            "
            :disabled="isRegistrationButtonDisabled"
          >
            <svg
              v-if="trainingActionLoading"
              class="animate-spin h-4 w-4 text-white"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
              ></circle>
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4l-3 3 3 3h-4z"
              ></path>
            </svg>
            <span>
              {{ registrationButtonText }}
            </span>
          </button>
          <p v-if="trainingActionError" class="training-modal-error text-center">
            {{ trainingActionError }}
          </p>
        </div>
      </div>
    </div>

    <!-- Career Details Modal -->
    <dialog v-if="showCareerPopup && selectedCareerDetails" open class="modal sm:modal-middle">
      <div class="modal-box max-w-6xl w-full relative font-poppins bg-white text-gray-900 max-h-[90vh] overflow-y-auto">
        <!-- Close button -->
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2 text-gray-600 hover:text-gray-800 z-10"
          @click="closeCareerModal">
          ✕
        </button>

        <!-- Career Details -->
        <div class="pr-8">
          <h2 class="text-xl font-bold mb-2">
            {{ selectedCareerDetails.position }}
          </h2>
          <p class="text-sm text-gray-400 mb-4">
            Organization: {{ selectedCareerDetails.organization }}
          </p>

          <!-- Career Details -->
          <div class="space-y-3 text-sm mb-6">
            <p>
              <strong>Place of Assignment:</strong>
              {{ selectedCareerDetails.placeOfAssignment || "Not specified" }}
            </p>
            <p v-if="selectedCareerDetails.details" class="career-text-inline">
              <strong>Details:</strong>
              <span class="career-text-value">{{ selectedCareerDetails.details }}</span>
            </p>
            <p v-if="selectedCareerDetails.qualificationStandard" class="career-text-inline">
              <strong>Qualification Standard:</strong>
              <span class="career-text-value">{{ selectedCareerDetails.qualificationStandard }}</span>
            </p>
            <p v-if="selectedCareerDetails.postingDate">
              <strong>Posting Date:</strong>
              {{ formatDate(selectedCareerDetails.postingDate) }}
            </p>
            <p v-if="selectedCareerDetails.closingDate">
              <strong>Closing Date:</strong>
              {{ formatDate(selectedCareerDetails.closingDate) }}
            </p>
            <p
              v-if="
                selectedCareerDetails.trainingsAttendedPercentage !== null &&
                selectedCareerDetails.trainingsAttendedPercentage !== undefined
              "
            >
              <strong>Training Match:</strong>
              {{ selectedCareerDetails.trainingsAttendedPercentage }}%
            </p>
            <p v-if="selectedCareerDetails.pdf_directory">
              <button class="text-blue-400 underline hover:text-blue-200" @click.prevent="viewCareerPDF">
                Attachments
              </button>
            </p>
          </div>

          <!-- Button on top of Recommended Trainings - full width -->
          <div class="mt-6 mb-6">
            <button v-if="!myApplications.has(selectedCareerDetails.careerID)"
              class="btn w-full bg-blue-600 text-white hover:bg-blue-700"
              @click="openApplyModal(selectedCareerDetails)">
              APPLY
            </button>
            <button v-else class="btn w-full bg-gray-500 text-white hover:bg-gray-600" @click="cancelApplication(selectedCareerDetails)">
              Cancel Application
            </button>
          </div>

          <!-- Recommended Trainings -->
          <div class="mt-6">
            <h3 class="text-base font-semibold mb-4">Recommended Trainings</h3>
            <div v-if="recommendedTrainings.length === 0" class="text-gray-400 text-sm">
              No recommended trainings available.
            </div>
            <div v-else class="flex overflow-x-auto space-x-4 pb-4 snap-x snap-mandatory" style="scrollbar-width: thin">
              <div
                v-for="training in recommendedTrainings"
                :key="training.trainingID"
                class="snap-start w-[300px] flex-shrink-0 p-4 bg-white text-gray-800 rounded-lg cursor-pointer hover:bg-gray-200 transition shadow-md border border-gray-200 relative"
                @click.stop="openTrainingModal(training)"
              >
                <!-- Org's Choice Badge -->
                <div
                  v-if="training.isOrganizationsChoice"
                  class="absolute top-3 right-3 px-2.5 py-1 text-[10px] font-bold text-yellow-800 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-full shadow-md border border-yellow-600 z-10 whitespace-nowrap"
                  title="Organization's Choice"
                >
                  ⭐ Org's Choice
                </div>
                <div class="flex items-start justify-between gap-2 mb-2">
                  <h4 class="font-semibold text-base leading-snug pr-20">
                    {{ training.title }}
                  </h4>
                </div>
                <p class="text-xs text-gray-600 truncate mt-2">
                  {{
                    training.organizationName ||
                    training.provider ||
                    training.name ||
                    "Unknown"
                  }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </dialog>

    <!-- Training Details Modal -->
    <dialog v-if="showTrainingModal && selectedTraining" open class="modal sm:modal-middle">
      <div class="modal-box max-w-3xl relative font-poppins bg-gray-800 text-white flex flex-col" style="max-height: 90vh;">
        <!-- Close button -->
        <button class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2 text-white z-10"
          @click="closeTrainingModal">
          ✕
        </button>

        <!-- Training Details -->
        <div class="flex-1 overflow-y-auto pr-4">
          <h2 class="text-xl font-bold mb-2">{{ selectedTraining.title }}</h2>
          <p class="text-sm text-gray-400 mb-2">
            Organization:
            {{
              selectedTraining.organizationName ||
              selectedTraining.provider ||
              selectedTraining.name ||
              "Unknown"
            }}
          </p>

          <!-- Divider -->
          <div class="divider my-4"></div>

          <!-- Training Details -->
          <div class="space-y-2 text-sm">
            <p>
              <strong>Mode:</strong>
              {{ selectedTraining.mode || "Not specified" }}
            </p>
            <p>
              <strong>Schedule:</strong>
              {{ formatDateTime(selectedTraining.schedule) }}
            </p>
            <p v-if="selectedTraining.location">
              <strong>Location:</strong> {{ selectedTraining.location }}
            </p>
            <p v-if="selectedTraining.trainingLink">
              <strong>Training Link:</strong>
              {{ selectedTraining.trainingLink }}
            </p>
            <p>
              <strong>Description:</strong> {{ selectedTraining.description }}
            </p>
          </div>
        </div>

        <!-- Button at bottom - full width -->
        <div class="mt-4 pt-4 border-t border-gray-600">
          <button v-if="!myRegistrations.has(selectedTraining.trainingID)"
            class="btn w-full bg-blue-600 text-white hover:bg-blue-700"
            @click="registerForTraining(selectedTraining)">
            REGISTER
          </button>
          <button v-else class="btn w-full bg-gray-500 text-white hover:bg-gray-600" @click="unregisterFromTraining(selectedTraining)">
            Unregister
          </button>
        </div>
      </div>
    </dialog>

    <!-- Apply Modal -->
    <dialog v-if="applyModalOpen" open class="modal sm:modal-middle">
      <div class="modal-box max-w-lg relative font-poppins">
        <button class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeApplyModal">
          ✕
        </button>

        <h2 class="text-xl font-bold mb-4">
          Apply for {{ selectedPost?.position }}
        </h2>

        <form @submit.prevent="submitApplication">
          <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Upload PDF Requirements</label>
            <input type="file" accept="application/pdf" @change="handleFileUpload" required
              class="file-input file-input-bordered w-full" />
          </div>
        <div class="flex justify-end gap-2">
          <button
            type="button"
            class="btn btn-outline btn-sm flex items-center justify-center gap-2"
            :disabled="unapplyLoading"
            @click="unapplyApplication"
          >
            <span v-if="!unapplyLoading">Cancel</span>
            <span v-else class="flex items-center gap-2">
              <svg class="animate-spin h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                  viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"></path>
              </svg>
              Withdrawing
            </span>
          </button>

          <button
            type="submit"
            class="btn bg-customButton hover:bg-dark-slate text-white btn-sm flex items-center justify-center gap-2"
            :disabled="isSubmitting"
          >
            <span v-if="!isSubmitting">Submit</span>
            <span v-else class="flex items-center gap-2">
              <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                  viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"></path>
              </svg>
              Processing
            </span>
          </button>
        </div>
        </form>
      </div>
    </dialog>

    <!-- Conflict Confirmation Dialog -->
    <dialog v-if="showConflictDialog" open class="modal sm:modal-middle">
      <div class="modal-box max-w-lg relative font-poppins">
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="cancelConflictRegistration"
        >
          ✕
        </button>

        <h2 class="text-xl font-bold mb-4 text-orange-600">
          ⚠️ Schedule Conflict Detected
        </h2>

        <div class="mb-4">
          <p class="text-gray-700 mb-3">
            You are trying to register for an <strong>onsite training</strong> that is scheduled on the same day as another onsite training you're already registered for.
          </p>

          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-3">
            <p class="font-semibold text-gray-800 mb-2">New Training:</p>
            <p class="text-gray-700">{{ conflictTraining?.title || "Training" }}</p>
            <p v-if="conflictTraining?.schedules?.[0]?.schedule" class="text-sm text-gray-600 mt-1">
              Date: {{ formatDate(conflictTraining.schedules[0].schedule) }}
            </p>
          </div>

          <div class="bg-red-50 border border-red-200 rounded-lg p-3">
            <p class="font-semibold text-gray-800 mb-2">Conflicting Training(s):</p>
            <div v-for="(conflict, index) in conflictingTrainings" :key="index" class="mb-2 last:mb-0">
              <p class="text-gray-700">{{ conflict.training?.title || "Training" }}</p>
              <p v-if="conflict.schedule?.schedule" class="text-sm text-gray-600">
                Date: {{ formatDate(conflict.schedule.schedule) }}
              </p>
              <p v-if="conflict.schedule?.location" class="text-sm text-gray-600">
                Location: {{ conflict.schedule.location }}
              </p>
            </div>
          </div>

          <p class="text-gray-700 mt-4 font-medium">
            Do you still want to register for this training?
          </p>
        </div>

        <div class="flex justify-end gap-2 mt-4">
          <button
            type="button"
            class="btn btn-outline btn-sm"
            @click="cancelConflictRegistration"
          >
            Cancel
          </button>
          <button
            type="button"
            class="btn bg-customButton hover:bg-dark-slate text-white btn-sm"
            @click="confirmConflictRegistration"
          >
            Yes, Register Anyway
          </button>
        </div>
      </div>
    </dialog>

    <!-- Toast Notifications -->
    <div class="toast toast-end toast-top z-50">
      <div v-for="toast in toasts" :key="toast.id" class="alert" :class="{
        'alert-info': toast.type === 'info',
        'alert-success': toast.type === 'success',
        'alert-error': toast.type === 'error',
        'alert-warning': toast.type === 'accent',
      }">
        {{ toast.message }}
      </div>
    </div>
  </div>
</template>

<style>
.event-day {
  background-color: #fde68a;
  border-radius: 9999px;
  font-weight: bold;
}
.selected-day {
  background-color: #3b82f6;
  color: white;
  border-radius: 9999px;
}

.today {
  border: 2px solid #4caf50;
  background-color: #e8f5e9;
  border-radius: 50%;
}

.training-modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.4);
  z-index: 1100;
  display: flex;
  align-items: center;
  justify-content: center;
}

.training-modal-box {
  background: #ffffff;
  color: #1f2937;
  width: min(95vw, 900px);
  max-height: 90vh;
  border-radius: 1rem;
  padding: 2rem;
  position: relative;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
}

.training-modal-close {
  position: absolute;
  top: 12px;
  right: 12px;
  border: none;
  background: transparent;
  font-size: 1.25rem;
  cursor: pointer;
  color: #6b7280;
}

.training-modal-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.75rem;
  color: #111827;
}

.training-modal-info {
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.training-modal-actions {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.5rem;
  margin: 1rem 0 1.5rem;
}

.training-modal-error {
  font-size: 0.8rem;
  color: #dc2626;
}

.training-modal-section-title {
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 0.5rem;
}

.training-schedules-container {
  margin-bottom: 1rem;
}

.training-schedules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 0.75rem;
}

.training-schedule-card {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem;
  padding: 0.85rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.training-schedule-empty {
  text-align: center;
  color: #6b7280;
}

.training-schedule-date,
.training-schedule-extra {
  display: flex;
  align-items: center;
  gap: 0.45rem;
  font-size: 0.9rem;
  color: #374151;
}

.training-schedule-icon {
  width: 18px;
  height: 18px;
  color: #2563eb;
  flex-shrink: 0;
}

.training-schedule-mode {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.3rem 0.75rem;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
  width: fit-content;
}

.training-mode-icon {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

.mode-onsite {
  background: #dbeafe;
  color: #1d4ed8;
}

.mode-online {
  background: #fef3c7;
  color: #b45309;
}

.training-schedule-link {
  color: #2563eb;
  text-decoration: underline;
  word-break: break-all;
}

/* Career text inline format to prevent overlap */
.career-text-inline {
  display: flex;
  flex-direction: row;
  align-items: flex-start;
  gap: 0.5rem;
  margin: 0.5rem 0;
  width: 100%;
}

.career-text-inline strong {
  flex-shrink: 0;
  white-space: nowrap;
}

.career-text-value {
  flex: 1;
  min-width: 0;
  word-wrap: break-word;
  overflow-wrap: break-word;
  word-break: break-word;
  white-space: pre-wrap;
  line-height: 1.6;
}
</style>
