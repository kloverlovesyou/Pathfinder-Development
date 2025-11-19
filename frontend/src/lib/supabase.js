import { createClient } from "@supabase/supabase-js";

const supabaseUrl = import.meta.env.VITE_SUPABASE_URL || "https://hmevengvfponcwslnyye.supabase.co";
// Try service_role key first (bypasses RLS), fallback to anon key
// Note: Service role key should be kept secure, but for file uploads it's often needed
const supabaseKey = import.meta.env.VITE_SUPABASE_SERVICE_ROLE_KEY || import.meta.env.VITE_SUPABASE_ANON_KEY || import.meta.env.VITE_SUPABASE_KEY;

export const supabase = createClient(supabaseUrl, supabaseKey);

/**
 * Upload PDF file to Supabase Storage
 * @param {File} file - The PDF file to upload
 * @param {string} bucketName - The bucket name (default: 'Requirements')
 * @returns {Promise<string|null>} - Returns the file path if successful, null otherwise
 */
export async function uploadPDF(file, bucketName = "Requirements") {
  if (!file) return null;

  if (file.type !== "application/pdf") {
    console.error("Only PDF files are allowed.");
    return null;
  }

  // Generate unique filename
  const fileName = `requirement_directory/${Date.now()}_${file.name}`;

  try {
    const { data, error } = await supabase.storage
      .from(bucketName)
      .upload(fileName, file, {
        cacheControl: "3600",
        upsert: false, // Don't overwrite existing files
      });

    if (error) {
      console.error("Supabase upload error:", error);
      console.error("Attempted bucket name:", bucketName);
      
      // Provide helpful error messages
      if (error.message?.includes("Bucket not found") || error.message?.includes("not found")) {
        console.error("Bucket Not Found Error:");
        console.error(`The bucket '${bucketName}' does not exist in Supabase.`);
        console.error("Please check:");
        console.error("1. Go to Supabase Dashboard → Storage → Buckets");
        console.error("2. Verify the exact bucket name (case-sensitive!)");
        console.error("3. Make sure the bucket exists and is spelled correctly");
      } else if (error.message?.includes("row-level security") || error.message?.includes("RLS")) {
        console.error("RLS Policy Error: Make sure your Supabase bucket is either:");
        console.error("1. Set to Public, OR");
        console.error("2. Has RLS policies that allow INSERT operations");
        console.error("Go to: Supabase Dashboard → Storage → [Your Bucket] → Policies");
      }
      
      return null;
    }

    return fileName; // Return the path stored in Supabase
  } catch (error) {
    console.error("Upload error:", error);
    return null;
  }
}

/**
 * Get public URL for a PDF file
 * @param {string} fileName - The file path in Supabase
 * @param {string} bucketName - The bucket name (default: 'Requirements')
 * @returns {string} - Public URL to the file
 */
export function getPDFUrl(fileName, bucketName = "Requirements") {
  const { data } = supabase.storage.from(bucketName).getPublicUrl(fileName);
  return data.publicUrl;
}

