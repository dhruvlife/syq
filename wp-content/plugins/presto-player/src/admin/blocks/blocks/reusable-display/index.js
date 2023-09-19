// In your application's entrypoint
const { InnerBlocks, useBlockProps } = wp.blockEditor;
import edit from "./edit";

/**
 * Block Name
 */
export const name = "presto-player/reusable-display";

/**
 * Block Options
 */
export const options = {
  title: "Reusable Block Display",

  category: "presto",

  attributes: {
    id: Number,
  },

  supports: {
    align: true,
    inserter: false,
    reusable: false,
    html: false,
  },

  usesContext: ["presto-player/playlist-media-id"],

  icon: (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
      className="presto-block-icon"
    >
      <circle cx="12" cy="12" r="10"></circle>
      <line x1="2" y1="12" x2="22" y2="12"></line>
      <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
    </svg>
  ),

  edit,

  // dynamic save function
  save: function () {
    return null;
  },
};
