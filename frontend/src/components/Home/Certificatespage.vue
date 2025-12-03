<script setup>
import { useRouter } from "vue-router";
import axios from "axios";
import { ref, onMounted, onUnmounted } from "vue";
import { useActivityStore } from "@/stores/activityStore";

const toasts = ref([]);
const router = useRouter();
const activityStore = useActivityStore();

const certificates = ref([]); // Pending uploads
const uploadedCertificates = ref([]); // Successfully uploaded
const userName = ref("");
const isModalOpen = ref(false);
const selectedImage = ref(null);
const selectedTitle = ref(null);
let certificateRefreshInterval = null;
const loading = ref(true);
const loadingUploads = ref([]); // Array to track loading state per certificate


// ➤ Add a new upload entry
function addCertificate() {
  certificates.value.push({
    title: "",
    image: null,
    file: null,
  });
  loadingUploads.value.push(false); // Add corresponding loading flag
}

function ensureCertificateSlot() {
  if (certificates.value.length === 0) {
    addCertificate();
  }
}

ensureCertificateSlot();

// ➤ Remove an upload entry
function removeCertificate(index) {
  certificates.value.splice(index, 1);
  loadingUploads.value.splice(index, 1); // Remove corresponding loading flag
  ensureCertificateSlot();
}

// Start certificate refresh interval
function startCertificateRefresh() {
  const savedUser = localStorage.getItem("user");
  if (savedUser) {
    const user = JSON.parse(savedUser);
    if (user.applicantID) {
      // Refresh certificates every 30 seconds to check for new organization-issued certificates
      certificateRefreshInterval = setInterval(() => {
        fetchCertificates(user.applicantID);
      }, 30 * 1000);
    }
  }
}

// Stop certificate refresh interval
function stopCertificateRefresh() {
  if (certificateRefreshInterval) clearInterval(certificateRefreshInterval);
}

// ➤ File preview handler
function handleFileUpload(event, index) {
  const file = event.target.files[0];
  if (!file) return;

  // Validate file type (jpeg, png, jpg)
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
  if (!allowedTypes.includes(file.type)) {
    showToast('Please upload a JPEG or PNG image file.', 'error');
    event.target.value = ''; // Clear the input
    return;
  }

  // Validate file size (max 4MB)
  const maxSize = 4 * 1024 * 1024; // 4MB in bytes
  if (file.size > maxSize) {
    showToast('File size must be less than 4MB.', 'error');
    event.target.value = ''; // Clear the input
    return;
  }

  certificates.value[index].file = file;

  const reader = new FileReader();
  reader.onload = (e) => {
    certificates.value[index].image = e.target.result;
  };
  reader.onerror = () => {
    showToast('Failed to read file. Please try again.', 'error');
    event.target.value = ''; // Clear the input
    certificates.value[index].file = null;
    certificates.value[index].image = null;
  };
  reader.readAsDataURL(file);
}

// ➤ Fetch certificates using the same method as Profilepage - from registrations via Supabase
async function fetchCertificates(applicantID) {
  try {
    const token = localStorage.getItem("token");
    
    if (!token) {
      console.error("❌ No token found in localStorage");
      showToast("Please log in to view certificates", "error");
      setTimeout(() => {
        router.push({ name: "Login" });
      }, 2000);
      return;
    }
    
    // Step 1: Fetch all certificates from /certificates endpoint (includes both manual and organization)
    let manualCertificates = [];
    let apiOrganizationCertificates = {}; // Map by certificate_path to get IsSelected values
    try {
      const certResponse = await axios.get(
        import.meta.env.VITE_API_BASE_URL + `/certificates/${applicantID}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );
      
      const allCerts = certResponse.data || [];
      
      // Separate manual and organization certificates
      allCerts.forEach(cert => {
        // Check source field first (set by backend)
        const isOrganization = cert.source === 'organization';
        const isManual = cert.source === 'manual';
        
        if (isOrganization && cert.certificate_path) {
          // Store organization certificate data by certificate_path for matching later
          apiOrganizationCertificates[cert.certificate_path] = {
            certificationID: cert.certificationID,
            IsSelected: cert.IsSelected === 1 || cert.IsSelected === true,
            certificationName: cert.certificationName,
            certificate: cert.certificate,
          };
        } else if (isManual) {
          // Manual certificate (can have certificate_path with Supabase URL or old binary data)
          manualCertificates.push({
            certificationID: cert.certificationID,
            certificationName: cert.certificationName,
            image: cert.certificate, // Supabase URL or data URL for old uploads
            IsSelected: cert.IsSelected === 1 || cert.IsSelected === true,
            source: 'manual',
            certificate_path: cert.certificate_path || null,
          });
        } else if (cert.certificate && !cert.certificate.startsWith('http')) {
          // Legacy manual certificate with binary data (data URL)
          manualCertificates.push({
            certificationID: cert.certificationID,
            certificationName: cert.certificationName,
            image: cert.certificate, // Data URL for old manual uploads
            IsSelected: cert.IsSelected === 1 || cert.IsSelected === true,
            source: 'manual',
            certificate_path: cert.certificate_path || null,
          });
        }
      });
      
      console.log('✅ Manual certificates loaded:', manualCertificates.length);
      console.log('✅ Organization certificates from API:', Object.keys(apiOrganizationCertificates).length);
    } catch (error) {
      console.warn('⚠️ Could not fetch certificates:', error.message);
    }
    
    // Step 2: Fetch organization-issued certificates from registrations (same method as Profilepage)
    const { getPDFUrl } = await import("@/lib/supabase");
    
    try {
      const regResponse = await axios.get(
        import.meta.env.VITE_API_BASE_URL + "/registrations",
        { headers: { Authorization: `Bearer ${token}` } }
      );
      
      console.log('📋 Registrations API response:', regResponse.data?.length || 0, 'registrations');
      
      // Filter registrations for this applicant with certificates
      const registrations = (regResponse.data || []).filter(reg => {
        return reg.applicantID === applicantID && 
               (reg.certificatePath || reg.certificate) &&
               reg.certificatePath !== null && 
               reg.certificatePath !== '';
      });
      
      console.log('📝 Registrations with certificates for applicantID', applicantID, ':', registrations.length);
      
      // Process each registration to get certificate URL from Supabase
      const organizationCertificates = await Promise.all(
        registrations.map(async (registration) => {
          const certificatePath = registration.certificatePath || registration.certificate;
          
          if (!certificatePath) {
            return null;
          }
          
          // Get Supabase public URL using the same method as Profilepage
          const supabaseUrl = getPDFUrl(certificatePath, "Requirements");
          
          // Get training title for certificate name
          const trainingTitle = registration.title || 
                               registration.training?.title || 
                               `Training #${registration.trainingID || 'Unknown'}`;
          const certificationName = `${trainingTitle} - Certificate of Completion`;
          
          // Check if this certificate exists in API response (has Certification entry)
          const existingCertFromAPI = apiOrganizationCertificates[certificatePath];
          
          console.log('📄 Processing registration certificate:', {
            registrationID: registration.registrationID,
            certificatePath: certificatePath,
            supabaseUrl: supabaseUrl,
            certificationName: certificationName,
            existingCertFromAPI: existingCertFromAPI ? 'found' : 'not found'
          });
          
          return {
            certificationID: existingCertFromAPI?.certificationID || ('REG_' + registration.registrationID),
            certificationName: existingCertFromAPI?.certificationName || certificationName,
            image: existingCertFromAPI?.certificate || supabaseUrl, // Use API URL if available, else Supabase URL
            IsSelected: existingCertFromAPI ? existingCertFromAPI.IsSelected : false, // Use existing IsSelected if available
            source: 'organization',
            registrationID: registration.registrationID,
            certGivenDate: registration.certGivenDate || null,
            certificate_path: certificatePath,
          };
        })
      );
      
      // Filter out null entries
      const validOrgCertificates = organizationCertificates.filter(cert => cert !== null);
      console.log('✅ Organization certificates loaded:', validOrgCertificates.length);
      
      // Combine manual and organization certificates
      uploadedCertificates.value = [...manualCertificates, ...validOrgCertificates];
      
      console.log('✅ Total certificates loaded:', uploadedCertificates.value.length);
      console.log('  - Manual:', manualCertificates.length);
      console.log('  - Organization:', validOrgCertificates.length);
      
      if (uploadedCertificates.value.length === 0) {
        console.log('ℹ️ No certificates found for applicantID:', applicantID);
      }
      
    } catch (error) {
      console.error("❌ Error fetching registrations:", error.response?.data || error);
      
      // Still show manual certificates if available
      uploadedCertificates.value = manualCertificates;
      
      if (error.response?.status === 401) {
        showToast("Your session has expired. Please log in again.", "error");
        localStorage.removeItem("token");
        localStorage.removeItem("user");
        setTimeout(() => {
          router.push({ name: "Login" });
        }, 2000);
        return;
      }
      
      showToast("Failed to load some certificates. Please try again.", "error");
    }
    
  } catch (error) {
    console.error("❌ Error fetching certificates:", error.response?.data || error);
    
    if (error.response?.status === 401) {
      showToast("Your session has expired. Please log in again.", "error");
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      setTimeout(() => {
        router.push({ name: "Login" });
      }, 2000);
      return;
    }
    
    showToast("Failed to load certificates. Please try again.", "error");
  }
}

// ➤ Upload a new certificate
async function uploadCertificate(cert, index) {
  const token = localStorage.getItem("token");
  const savedUser = localStorage.getItem("user");
  const user = savedUser ? JSON.parse(savedUser) : null;

  if (!token || !user?.applicantID) {
    showToast("Please log in again.", "error");
    setTimeout(() => router.push({ name: "Login" }), 1500);
    return;
  }

  if (!cert.file) {
    showToast("Please select an image before uploading.", "error");
    return;
  }

  // Validate file type (jpeg, png, jpg)
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
  if (!allowedTypes.includes(cert.file.type)) {
    showToast("Please upload a JPEG or PNG image file.", "error");
    return;
  }

  // Validate file size (max 4MB = 4096 KB)
  const maxSize = 4 * 1024 * 1024; // 4MB in bytes
  if (cert.file.size > maxSize) {
    showToast("File size must be less than 4MB.", "error");
    return;
  }

  const trimmedTitle = cert.title?.trim();
  if (!trimmedTitle) {
    showToast("Please enter a certificate title.", "error");
    return;
  }

  // Validate title length (max 255 characters as per database schema)
  if (trimmedTitle.length > 255) {
    showToast("Certificate title must be 255 characters or less.", "error");
    return;
  }

  const formData = new FormData();
  formData.append("certificationName", trimmedTitle);
  formData.append("applicantID", user.applicantID.toString());
  formData.append("IsSelected", "1");
  formData.append("certificate", cert.file);

  try {
    const response = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/certificates",
      formData,
      {
        headers: { 
          Authorization: `Bearer ${token}`,
          // Don't set Content-Type manually - axios will set it with boundary for FormData
        },
      }
    );

    // Remove the uploaded certificate from pending list
    certificates.value.splice(index, 1);
    ensureCertificateSlot();
    
    // Refresh the certificates list
    await fetchCertificates(user.applicantID);
    
    showToast(`Certificate "${trimmedTitle}" uploaded successfully!`, "success");
  } catch (error) {
    console.error("❌ Upload error:", error.response?.data || error);
    
    // Handle specific error cases
    let message = "Failed to upload certificate";
    
    if (error.response?.status === 422) {
      // Validation errors
      const errors = error.response.data?.errors;
      if (errors) {
        const firstError = Object.values(errors)[0];
        message = Array.isArray(firstError) ? firstError[0] : firstError;
      } else {
        message = error.response.data?.message || message;
      }
    } else if (error.response?.status === 401) {
      message = "Your session has expired. Please log in again.";
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      setTimeout(() => router.push({ name: "Login" }), 2000);
    } else if (error.response?.status === 413) {
      message = "File is too large. Maximum size is 4MB.";
    } else if (error.response?.data?.message) {
      message = error.response.data.message;
    } else if (error.message) {
      message = error.message;
    }
    
    showToast(message, "error");
  }
}

const showDeleteModal = ref(false);
const certToDelete = ref(null);

function confirmDeleteCertificate(cert) {
  certToDelete.value = cert;
  showDeleteModal.value = true;
}

async function deleteCertificateConfirmed() {
  const token = localStorage.getItem("token");
  const id = certToDelete.value.certificationID || certToDelete.value.id;
  const source = certToDelete.value.source || 'manual';

  // Prevent deletion of organization-issued certificates
  if (source === 'organization' || id?.toString().startsWith('REG_')) {
    showToast("Cannot delete organization-issued certificates", "error");
    showDeleteModal.value = false;
    certToDelete.value = null;
    return;
  }

  try {
    await axios.delete(import.meta.env.VITE_API_BASE_URL +`/certificates/${id}`, {
      headers: { Authorization: `Bearer ${token}` },
    });

    // Remove locally
    uploadedCertificates.value = uploadedCertificates.value.filter(
      (c) => c.certificationID !== id && c.id !== id
    );

    showToast("Certificate deleted successfully", "success");
  } catch (error) {
    console.error("❌ Delete error:", error.response?.data || error);
    showToast("Failed to delete certificate", "error");
  } finally {
    showDeleteModal.value = false;
    certToDelete.value = null;
  }
}

// ✅ Toggle certificate selection for resume
async function toggleCertificateSelection(cert) {
  const token = localStorage.getItem("token");
  const source = cert.source || 'manual';
  const id = cert.certificationID || cert.id;
  const user = JSON.parse(localStorage.getItem("user"));

  // Store original state for error recovery
  const originalIsSelected = cert.IsSelected;

  // For organization-issued certificates, create/update Certification entry and toggle
  if (source === 'organization' || id?.toString().startsWith('REG_')) {
    // Validate required fields
    if (!cert.certificate_path) {
      console.error("❌ Certificate path is missing:", cert);
      showToast("Certificate path is missing. Cannot update selection.", "error");
      return;
    }

    try {
      // Update local state immediately for better UX
      cert.IsSelected = !cert.IsSelected;
      
      // Check if Certification entry exists by fetching all certificates
      const certResponse = await axios.get(
        import.meta.env.VITE_API_BASE_URL + `/certificates/${user.applicantID}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );
      
      // Find existing certification by certificate_path (must have real certificationID, not REG_)
      const existingCert = (certResponse.data || []).find(
        c => c.certificate_path && 
             cert.certificate_path &&
             c.certificate_path === cert.certificate_path && 
             c.certificationID && 
             !c.certificationID.toString().startsWith('REG_')
      );
      
      if (existingCert) {
        // Toggle existing Certification entry
        const toggleResponse = await axios.patch(
          import.meta.env.VITE_API_BASE_URL + `/certificates/${existingCert.certificationID}/toggle`,
          {},
          { 
            headers: { 
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json'
            } 
          }
        );
        
        const newIsSelected = toggleResponse.data.IsSelected === 1 || toggleResponse.data.IsSelected === true;
        cert.IsSelected = newIsSelected;
        cert.certificationID = existingCert.certificationID; // Update ID to real certificationID
        
        console.log('✅ Toggled existing Certification entry:', {
          certificationID: existingCert.certificationID,
          IsSelected: newIsSelected
        });
      } else {
        // Need to create or update Certification entry
        // The backend store method will check if one exists and update it if needed
        // Use JSON instead of FormData since we're not uploading a file
        const requestData = {
          certificationName: cert.certificationName,
          certificate_path: cert.certificate_path,
          applicantID: user.applicantID,
          IsSelected: cert.IsSelected ? 1 : 0,
        };
        
        const createResponse = await axios.post(
          import.meta.env.VITE_API_BASE_URL + '/certificates',
          requestData,
          { 
            headers: { 
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json'
            } 
          }
        );
        
        // Handle response - backend might return updated existing certificate
        const newCertificationID = createResponse.data.data?.certificationID || 
                                   createResponse.data.certificationID;
        const newIsSelected = createResponse.data.data?.IsSelected ?? 
                             (cert.IsSelected ? 1 : 0);
        
        cert.IsSelected = newIsSelected === 1 || newIsSelected === true;
        cert.certificationID = newCertificationID;
        
        console.log('✅ Created/Updated Certification entry:', {
          certificationID: newCertificationID,
          IsSelected: cert.IsSelected,
          message: createResponse.data.message
        });
      }
      
      showToast(
        cert.IsSelected
          ? `"${cert.certificationName}" added to resume!`
          : `"${cert.certificationName}" removed from resume!`,
        "success"
      );
      
      // Refresh certificates to get updated data
      await fetchCertificates(user.applicantID);
      
    } catch (error) {
      console.error("❌ Toggle organization certificate error:", {
        error: error.response?.data || error.message,
        status: error.response?.status,
        fullError: error,
        certificate: {
          certificationName: cert.certificationName,
          certificate_path: cert.certificate_path,
          certificationID: cert.certificationID
        }
      });
      
      // Revert local state on error
      cert.IsSelected = originalIsSelected;
      
      // Show more specific error message - prioritize backend error messages
      let errorMessage = "Failed to update selection. Please try again.";
      
      if (error.response?.data) {
        // Check for validation errors
        if (error.response.data.errors) {
          const firstError = Object.values(error.response.data.errors)[0];
          errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
        } 
        // Check for general error message
        else if (error.response.data.message) {
          errorMessage = error.response.data.message;
        }
        // Check for error field
        else if (error.response.data.error) {
          errorMessage = error.response.data.error;
        }
      } else if (error.response?.status === 404) {
        errorMessage = "Certificate not found";
      } else if (error.response?.status === 401) {
        errorMessage = "Please log in again";
      }
      
      showToast(errorMessage, "error");
    }
    return;
  }

  // For manually uploaded certificates, use existing toggle endpoint
  try {
    const response = await axios.patch(
      import.meta.env.VITE_API_BASE_URL +`/certificates/${id}/toggle`,
      {},
      { headers: { Authorization: `Bearer ${token}` } }
    );

    cert.IsSelected =
      response.data.IsSelected === 1 || response.data.IsSelected === true;

    showToast(
      cert.IsSelected
        ? `"${cert.certificationName}" added to resume!`
        : `"${cert.certificationName}" removed from resume!`,
      "success"
    );
  } catch (error) {
    console.error("❌ Toggle selection error:", error.response?.data || error);
    showToast("Failed to update selection", "error");
  }
}

// Modal handlers

function handleImageError(event) {
  console.error('Image failed to load:', {
    src: event.target.src?.substring(0, 100),
    error: event
  });
  
  // Try to find the certificate name for better error message
  const certName = event.target.alt || 'certificate';
  
  event.target.style.display = 'none';
  const parent = event.target.parentElement;
  if (parent) {
    parent.innerHTML = `
      <div class="p-4 text-center">
        <svg class="w-12 h-12 text-red-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <p class="text-sm text-red-600 font-semibold mb-1">Failed to load image</p>
        <p class="text-xs text-gray-500">Please try re-uploading this certificate</p>
      </div>
    `;
  }
}

function openModal(image, title) {
  selectedImage.value = image;
  selectedTitle.value = title;
  isModalOpen.value = true;
}
function closeModal() {
  isModalOpen.value = false;
}

// Logout
function logout() {
  localStorage.removeItem("user");
  router.push({ name: "Login" });
}

// Load user info and fetch certificates
onMounted(async () => {
  loading.value = true; // ⏳ Start loading

  const savedUser = localStorage.getItem("user");
  const token = localStorage.getItem("token");

  if (!token) {
    console.error("❌ No token found - redirecting to login");
    showToast("Please log in to view certificates", "error");
    setTimeout(() => router.push({ name: "Login" }), 2000);
    loading.value = false;
    return;
  }

  if (savedUser) {
    try {
      const user = JSON.parse(savedUser);

      userName.value =
        user.firstName && user.lastName
          ? `${user.firstName} ${user.lastName}`
          : "Guest";

      if (user.applicantID) {
        await fetchCertificates(user.applicantID);
        startCertificateRefresh(); 
      } else {
        console.warn("⚠️ No applicantID found in user data");
        showToast("User data incomplete. Please log in again.", "error");
        setTimeout(() => router.push({ name: "Login" }), 2000);
        loading.value = false;
        return;
      }
    } catch (error) {
      console.error("❌ Error parsing user data:", error);
      localStorage.removeItem("user");
      localStorage.removeItem("token");
      showToast("Session data corrupted. Please log in again.", "error");
      setTimeout(() => router.push({ name: "Login" }), 2000);
      loading.value = false;
      return;
    }
  } else {
    console.error("❌ No user data found - redirecting to login");
    showToast("Please log in to view certificates", "error");
    setTimeout(() => router.push({ name: "Login" }), 2000);
    loading.value = false;
    return;
  }

  await activityStore.fetchCounts();

  loading.value = false; // ✅ Stop loading
});

// Clean up intervals on unmount
onUnmounted(() => {
  stopCertificateRefresh();
});

// ➤ Toast helper
function showToast(message, type = "success", duration = 3000) {
  const id = Date.now();
  toasts.value.push({ id, message, type });

  setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
  }, duration);
}

const showManageMenu = ref(false);

const toggleManageMenu = () => {
  showManageMenu.value = !showManageMenu.value;
};

const selectAllCertificates = async () => {
  const token = localStorage.getItem("token");

  try {
    // Update each certificate both locally and in backend
    await Promise.all(
      uploadedCertificates.value.map(async (cert) => {
        if (!cert.IsSelected) {
          const source = cert.source || 'manual';
          const id = cert.certificationID || cert.id;
          
          // For organization-issued certificates, just toggle locally
          if (source === 'organization' || id?.toString().startsWith('REG_')) {
            cert.IsSelected = true;
          } else {
            // For manually uploaded certificates, update via API
            await axios.patch(
              import.meta.env.VITE_API_BASE_URL + `/certificates/${id}/toggle`,
              {},
              { headers: { Authorization: `Bearer ${token}` } }
            );
            cert.IsSelected = true;
          }
        }
      })
    );

    showToast("All certificates selected for resume!", "success");
  } catch (error) {
    console.error("Error selecting all certificates:", error);
    showToast("Failed to select all certificates", "error");
  } finally {
    showManageMenu.value = false;
  }
};

const deselectAllCertificates = async () => {
  const token = localStorage.getItem("token");

  try {
    // Update each certificate both locally and in backend
    await Promise.all(
      uploadedCertificates.value.map(async (cert) => {
        if (cert.IsSelected) {
          const source = cert.source || 'manual';
          const id = cert.certificationID || cert.id;
          
          // For organization-issued certificates, just toggle locally
          if (source === 'organization' || id?.toString().startsWith('REG_')) {
            cert.IsSelected = false;
          } else {
            // For manually uploaded certificates, update via API
            await axios.patch(
              import.meta.env.VITE_API_BASE_URL + `/certificates/${id}/toggle`,
              {},
              { headers: { Authorization: `Bearer ${token}` } }
            );
            cert.IsSelected = false;
          }
        }
      })
    );

    showToast("All certificates removed from resume!", "success");
  } catch (error) {
    console.error("Error deselecting all certificates:", error);
    showToast("Failed to deselect all certificates", "error");
  } finally {
    showManageMenu.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen p-3 rounded-lg font-poppins">
    <div class="min-h-screen font-poppins lg:flex">
      <!-- Sidebar -->
      <div
        v-if="false"
        class="w-full lg:w-1/4 bg-white rounded-lg shadow p-6 flex flex-col items-center hidden lg:flex"
      >
        <div class="w-24 h-24 rounded-full bg-white mb-4">
          <img
            src="https://img.daisyui.com/images/profile/demo/yellingcat@192.webp"
            alt="User Avatar"
            class="w-full h-full object-cover rounded-full"
          />
        </div>
        <h2 class="text-xl font-semibold mb-6">{{ userName }}</h2>

        <div
          class="w-full flex items-center justify-center gap-6 mb-6 relative"
        >
          <!-- Upcoming -->
          <div class="relative">
            <div
              class="flex items-center justify-center bg-gray-100 rounded-full px-6 py-2"
            >
              <span class="font-semibold text-gray-700">Upcoming</span>
            </div>
            <!-- Floating Bubble -->
            <span
              class="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-customButton rounded-full"
            >
              {{ activityStore.upcomingCount }}
            </span>
          </div>

          <!-- Completed -->
          <div class="relative">
            <div
              class="flex items-center justify-center bg-gray-100 rounded-full px-6 py-2"
            >
              <span class="font-semibold text-gray-700">Completed</span>
            </div>
            <!-- Floating Bubble -->
            <span
              class="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-customButton rounded-full"
            >
              {{ activityStore.completedCount  }}
            </span>
          </div>
        </div>

        <div class="w-full flex flex-col gap-3">
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="$router.push({ name: 'ResumeEditorpage' })"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 34 34"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M19.8333 3.21521V9.06681C19.8333 9.86022 19.8333 10.2569 19.9877 10.56C20.1235 10.8265 20.3402 11.0432 20.6068 11.1791C20.9098 11.3335 21.3066 11.3335 22.1 11.3335H27.9516M22.6666 18.4167H11.3333M22.6666 24.0834H11.3333M14.1666 12.75H11.3333M19.8333 2.83337H12.4666C10.0864 2.83337 8.89629 2.83337 7.98717 3.2966C7.18748 3.70406 6.53731 4.35423 6.12985 5.15391C5.66663 6.06304 5.66663 7.25315 5.66663 9.63337V24.3667C5.66663 26.7469 5.66663 27.937 6.12985 28.8462C6.53731 29.6459 7.18748 30.296 7.98717 30.7035C8.89629 31.1667 10.0864 31.1667 12.4666 31.1667H21.5333C23.9135 31.1667 25.1036 31.1667 26.0128 30.7035C26.8124 30.296 27.4626 29.6459 27.8701 28.8462C28.3333 27.937 28.3333 26.7469 28.3333 24.3667V11.3334L19.8333 2.83337Z"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            <span>Resume</span>
          </button>
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="$router.push({ name: 'Certificatespage' })"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 35 35"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M9.47917 29.1666H7.29167C5.68084 29.1666 4.375 27.8608 4.375 26.25V5.83329C4.375 4.22246 5.68084 2.91663 7.29167 2.91663H27.7083C29.3192 2.91663 30.625 4.22246 30.625 5.83329V26.25C30.625 27.8608 29.3192 29.1666 27.7083 29.1666H25.5208M17.5 27.7083C19.9162 27.7083 21.875 25.7495 21.875 23.3333C21.875 20.917 19.9162 18.9583 17.5 18.9583C15.0838 18.9583 13.125 20.917 13.125 23.3333C13.125 25.7495 15.0838 27.7083 17.5 27.7083ZM17.5 27.7083L17.5313 27.708L12.8751 32.3641L8.75036 28.2393L13.1537 23.836M17.5 27.7083L22.1562 32.3641L26.281 28.2393L21.8777 23.836M13.125 8.74996H21.875M10.2083 13.8541H24.7917"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            <span>Certificates</span>
          </button>

          <div class="divider"></div>
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="$router.push({ name: 'UpdateDeletepage' })"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M20.1 9.21994C18.29 9.21994 17.55 7.93994 18.45 6.36994C18.97 5.45994 18.66 4.29994 17.75 3.77994L16.02 2.78994C15.23 2.31994 14.21 2.59994 13.74 3.38994L13.63 3.57994C12.73 5.14994 11.25 5.14994 10.34 3.57994L10.23 3.38994C9.78 2.59994 8.76 2.31994 7.97 2.78994L6.24 3.77994C5.33 4.29994 5.02 5.46994 5.54 6.37994C6.45 7.93994 5.71 9.21994 3.9 9.21994C2.86 9.21994 2 10.0699 2 11.1199V12.8799C2 13.9199 2.85 14.7799 3.9 14.7799C5.71 14.7799 6.45 16.0599 5.54 17.6299C5.02 18.5399 5.33 19.6999 6.24 20.2199L7.97 21.2099C8.76 21.6799 9.78 21.3999 10.25 20.6099L10.36 20.4199C11.26 18.8499 12.74 18.8499 13.65 20.4199L13.76 20.6099C14.23 21.3999 15.25 21.6799 16.04 21.2099L17.77 20.2199C18.68 19.6999 18.99 18.5299 18.47 17.6299C17.56 16.0599 18.3 14.7799 20.11 14.7799C21.15 14.7799 22.01 13.9299 22.01 12.8799V11.1199C22 10.0799 21.15 9.21994 20.1 9.21994ZM12 15.2499C10.21 15.2499 8.75 13.7899 8.75 11.9999C8.75 10.2099 10.21 8.74994 12 8.74994C13.79 8.74994 15.25 10.2099 15.25 11.9999C15.25 13.7899 13.79 15.2499 12 15.2499Z"
                fill="white"
              />
            </svg>

            <span>Account Setting</span>
          </button>
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="logout"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M15.24 22.27H15.11C10.67 22.27 8.53002 20.52 8.16002 16.6C8.12002 16.19 8.42002 15.82 8.84002 15.78C9.24002 15.74 9.62002 16.05 9.66002 16.46C9.95002 19.6 11.43 20.77 15.12 20.77H15.25C19.32 20.77 20.76 19.33 20.76 15.26V8.73998C20.76 4.66998 19.32 3.22998 15.25 3.22998H15.12C11.41 3.22998 9.93002 4.41998 9.66002 7.61998C9.61002 8.02998 9.26002 8.33998 8.84002 8.29998C8.42002 8.26998 8.12001 7.89998 8.15001 7.48998C8.49001 3.50998 10.64 1.72998 15.11 1.72998H15.24C20.15 1.72998 22.25 3.82998 22.25 8.73998V15.26C22.25 20.17 20.15 22.27 15.24 22.27Z"
                fill="white"
              />
              <path
                d="M15.0001 12.75H3.62012C3.21012 12.75 2.87012 12.41 2.87012 12C2.87012 11.59 3.21012 11.25 3.62012 11.25H15.0001C15.4101 11.25 15.7501 11.59 15.7501 12C15.7501 12.41 15.4101 12.75 15.0001 12.75Z"
                fill="white"
              />
              <path
                d="M5.84994 16.1C5.65994 16.1 5.46994 16.03 5.31994 15.88L1.96994 12.53C1.67994 12.24 1.67994 11.76 1.96994 11.47L5.31994 8.12003C5.60994 7.83003 6.08994 7.83003 6.37994 8.12003C6.66994 8.41003 6.66994 8.89003 6.37994 9.18003L3.55994 12L6.37994 14.82C6.66994 15.11 6.66994 15.59 6.37994 15.88C6.23994 16.03 6.03994 16.1 5.84994 16.1Z"
                fill="white"
              />
            </svg>

            <span>Logout</span>
          </button>
        </div>
      </div>

      <!-- Right content -->
      <div class="w-full lg:pl-6 lg:mt-0 flex flex-col gap-6">
        <!-- Upload form -->
        <div class="border bg-white rounded-lg p-4 space-y-4 relative">
          <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">Upload Certificates</h2>
            <button
              type="button"
              @click="addCertificate"
              class="w-8 h-8 flex items-center justify-center rounded-full bg-customButton text-white hover:bg-dark-slate"
            >
              +
            </button>
          </div>

          <!-- Only show upload forms for pending uploads -->
          <div
            v-for="(cert, index) in certificates"
            :key="index"
            class="space-y-2 border p-3 rounded relative"
          >
            <button
              type="button"
              @click="removeCertificate(index)"
              class="absolute top-2 right-2 text-gray-500 hover:text-red-500"
            >
              ✕
            </button>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <input
                v-model="cert.title"
                type="text"
                placeholder="Certificate Title*"
                class="input-field border rounded p-2"
                maxlength="50"
              />
              <p v-if="cert.title.length >= 50" class="text-xs text-red-500 mt-1">
                Maximum character limit (50) reached
              </p>
              <p v-else-if="cert.title.length > 0" class="text-xs text-gray-500 mt-1">
                {{ 50 - cert.title.length }} characters remaining
              </p>
              <input
                type="file"
                accept="image/jpeg,image/jpg,image/png"
                class="file-input"
                @change="handleFileUpload($event, index)"
              />
            </div>

            

            <div
              v-if="cert.image"
              class="h-32 w-full flex items-center justify-center border rounded bg-gray-50"
            >
              <img
                :src="cert.image"
                alt="Preview"
                class="h-full w-full object-cover rounded-lg"
              />
            </div>

            <div
              v-else
              class="h-32 w-full flex items-center justify-center border rounded text-gray-400 text-sm"
            >
              Certificate Preview
            </div>

            <div class="flex justify-end mt-2">
              <button
                type="button"
                @click="uploadCertificate(cert, index)"
                class="px-4 py-2 bg-customButton text-white rounded hover:bg-dark-slate"
              >
                Upload
              </button>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-10 space-y-3">
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
          <p class="text-gray-600 text-sm">Loading certificates...</p>
        </div>

        <!-- Uploaded certificates display -->
        <div v-else class="bg-white rounded-lg shadow p-6 flex-1">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="text-gray-500">
              Select certificates to appear in the resume.
            </div>
            <div class="relative ml-auto">
              <button
                @click="toggleManageMenu"
                class="bg-customButton hover:bg-dark-slate text-white text-sm px-3 py-2 rounded-md transition"
              >
                Manage Selection ▾
              </button>

              <!-- Dropdown -->
              <div
                v-if="showManageMenu"
                class="absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded-md shadow-lg z-10"
              >
                <button
                  @click="selectAllCertificates"
                  class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100"
                >
                  Select All
                </button>
                <button
                  @click="deselectAllCertificates"
                  class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100"
                >
                  Deselect All
                </button>
              </div>
            </div>

            <div
              v-for="(cert, index) in uploadedCertificates"
              :key="'uploaded-' + index"
              class="flex flex-col bg-white shadow rounded-lg overflow-hidden relative"
            >
              <!-- Certificate Image -->
              <div
                class="w-full h-48 bg-gray-200 flex items-center justify-center cursor-pointer hover:opacity-90 transition overflow-hidden"
                @click="openModal(cert.image, cert.certificationName)"
              >
                <img
                  v-if="cert.image"
                  :src="cert.image"
                  alt="Certificate Image"
                  class="h-full w-full object-contain bg-white"
                  @error="handleImageError($event)"
                />
                <span v-else class="text-gray-500">No Certificate</span>
              </div>

              <!-- Bottom section -->
              <div class="flex justify-between items-center px-4 py-3">
                <p class="text-gray-800 font-medium truncate w-2/3">
                  {{ cert.certificationName }}
                </p>

                <div class="flex items-center gap-3">
                  <div class="flex items-center justify-center" title="Show on Resume">
                    <input
                      type="checkbox"
                      :checked="cert.IsSelected"
                      @change="() => toggleCertificateSelection(cert)"
                      class="w-4 h-4 accent-customButton cursor-pointer"
                    />
                  </div>

                  <button
                    v-if="cert.source !== 'organization' && !cert.certificationID?.toString().startsWith('REG_')"
                    @click.stop="confirmDeleteCertificate(cert)"
                    title="Delete certificate"
                  >
                    <svg
                      width="22"
                      height="22"
                      viewBox="0 0 24 24"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M7 21C6.45 21 5.97917 20.8042 5.5875 20.4125C5.19583 20.0208 5 19.55 5 19V6H4V4H9V3H15V4H20V6H19V19C19 19.55 18.8042 20.0208 18.4125 20.4125C18.0208 20.8042 17.55 21 17 21H7ZM17 6H7V19H17V6ZM9 17H11V8H9V17ZM13 17H15V8H13V17Z"
                        fill="#7A838F"
                      />
                    </svg>
                  </button>

                  <span
                    v-if="cert.source === 'organization' || cert.certificationID?.toString().startsWith('REG_')"
                    class="text-xs text-green-600 font-semibold px-2 py-1 bg-green-100 rounded"
                    title="Organization-issued certificate"
                  >
                    ✓ Issued
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
