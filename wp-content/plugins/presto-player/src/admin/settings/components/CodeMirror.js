/** @jsx jsx */
import { css, jsx } from "@emotion/core";
import { BaseControl } from "@wordpress/components";
import { useEffect, useRef } from "@wordpress/element";
import classNames from "classnames";

export default ({ option, value, className, disabled, onChange }) => {
  let codeMirror;

  const handleChange = (instance) => {
    if (disabled) {
      return;
    }
    instance.save();
    onChange(textRef.current.value);
  };

  const textRef = useRef();
  useEffect(() => {
    if (!wp?.CodeMirror) {
      return;
    }
    codeMirror = wp.CodeMirror.fromTextArea(textRef.current, {
      type: "text/css",
      lineNumbers: true,
    });

    codeMirror.on("change", handleChange);
  }, []);

  return (
    <div className={classNames(className, "presto-settings__setting")}>
      <BaseControl
        css={css`
          .CodeMirror {
            height: 200px;
            border: 1px solid #e3e3e3;
            border-radius: 4px;
          }
        `}
        label={option?.name}
        help={option?.help}
      >
        <textarea onChange={handleChange} ref={textRef} rows="5" disabled>
          {value}
        </textarea>
      </BaseControl>
    </div>
  );
};
