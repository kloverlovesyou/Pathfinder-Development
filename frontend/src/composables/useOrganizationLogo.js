import { ref, computed, onMounted } from 'vue';
import { getImageUrl } from "@/lib/supabase.js";

/**
 * Composable to get organization logo URL
 * Returns the organization's uploaded logo if available, otherwise returns null
 */
export function useOrganizationLogo() {
  const organizationLogo = ref(null);

  const logoUrl = computed(() => {
    if (organizationLogo.value) {
      const logoPath = organizationLogo.value.logo_directory || 
                      organizationLogo.value.Logo_directory || 
                      organizationLogo.value.logoPath;
      if (logoPath) {
        const url = getImageUrl(logoPath, "Requirements");
        return url || null;
      }
    }
    return null;
  });

  onMounted(() => {
    // Get organization logo from localStorage
    const storedUser = localStorage.getItem("user");
    if (storedUser) {
      try {
        const user = JSON.parse(storedUser);
        if (user.organization) {
          organizationLogo.value = user.organization;
        } else if (user.logo_directory || user.Logo_directory || user.logoPath) {
          organizationLogo.value = user;
        }
      } catch (error) {
        console.error("Error parsing user from localStorage:", error);
      }
    }
  });

  return {
    logoUrl,
    organizationLogo,
  };
}

