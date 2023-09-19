import {
  InnerBlocks,
  useBlockProps,
  useInnerBlocksProps,
} from "@wordpress/block-editor";
import { store as blockEditorStore } from "@wordpress/block-editor";
import { useDispatch, useSelect } from "@wordpress/data";
import { useEffect } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

export default ({ clientId, isSelected }) => {
  const { selectBlock } = useDispatch(blockEditorStore);
  const blockProps = useBlockProps({ slot: "list" });

  const innerBlocksProps = useInnerBlocksProps(blockProps, {
    renderAppender: InnerBlocks.ButtonBlockAppender,
    allowedBlocks: ["presto-player/reusable-display"],
    templateLock: false,
  });

  const {
    listCount,
    heading,
    listTextSingular,
    listTextPlural,
    parentClientId,
  } = useSelect(
    (select) => {
      const parentClientId =
        select("core/block-editor").getBlockHierarchyRootClientId(clientId);
      const parentAttributes =
        select("core/block-editor").getBlockAttributes(parentClientId);
      return {
        listCount: select("core/block-editor").getBlocks(clientId).length,
        heading: parentAttributes?.heading,
        listTextPlural: parentAttributes?.listTextPlural,
        listTextSingular: parentAttributes?.listTextSingular,
        parentClientId: parentClientId,
      };
    },
    [clientId]
  );

  // select parent block if this block is selected.
  useEffect(() => {
    if (isSelected) {
      selectBlock(parentClientId);
    }
  }, [isSelected]);

  return (
    <>
      <div slot="title">{heading}</div>
      <div slot="count">
        {listCount} {listCount > 1 ? listTextPlural : listTextSingular}
      </div>
      <div {...innerBlocksProps}></div>
    </>
  );
};
