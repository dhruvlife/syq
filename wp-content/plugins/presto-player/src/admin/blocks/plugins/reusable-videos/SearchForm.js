/**
 * External dependencies
 */
import classnames from "classnames";

/**
 * WordPress dependencies
 */
const { useInstanceId } = wp.compose;
const { __ } = wp.i18n;
const { VisuallyHidden, Button, Icon } = wp.components;
const { useRef } = wp.element;

function InserterSearchForm({ className, onChange, value, placeholder }) {
  const instanceId = useInstanceId(InserterSearchForm);
  const searchInput = useRef();

  return (
    <div className={"block-editor-inserter__search"} style={{ padding: 0 }}>
      <VisuallyHidden
        as="label"
        htmlFor={`block-editor-inserter__search-${instanceId}`}
      >
        {placeholder}
      </VisuallyHidden>
      <input
        ref={searchInput}
        className="block-editor-inserter__search-input"
        id={`block-editor-inserter__search-${instanceId}`}
        type="search"
        placeholder={placeholder}
        onChange={(event) => onChange(event.target.value)}
        autoComplete="off"
        value={value || ""}
      />
      <div className="block-editor-inserter__search-icon">
        {!!value && (
          <Button
            icon={"close-small"}
            label={__("Reset search", "presto-player")}
            onClick={() => {
              onChange("");
              searchInput.current.focus();
            }}
          />
        )}
        {!value && <Icon icon={"search"} />}
      </div>
    </div>
  );
}

export default InserterSearchForm;
