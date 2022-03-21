import {EditorState, basicSetup} from "@codemirror/basic-setup"
import {EditorView, ViewUpdate, keymap} from "@codemirror/view"
import {oneDarkTheme} from "@codemirror/theme-one-dark";
import {defaultTabBinding} from "@codemirror/commands"
import {html} from "@codemirror/lang-html"
import {css} from "@codemirror/lang-css"
import {javascript} from "@codemirror/lang-javascript"

$(document).ready(function () {
  $('.codemirror').each(function(){
    if ($(this).hasClass('htmlEditor')) {
      editor(this, html());
    } else if ($(this).hasClass('cssEditor')) {
      editor(this, css());
    } else if ($(this).hasClass('jsEditor')) {
      editor(this, javascript());
    }
  });

  function editor(div, type) {
    return new EditorView({
      state: EditorState.create({
        extensions: [
          basicSetup, 
          keymap.of([defaultTabBinding]),
          type,
          //oneDarkTheme,
          EditorView.updateListener.of((EditorView) => {
            $(div).find('textarea').text(EditorView.state.doc.toString());
          })
        ],
        doc: $(div).find('code').text()
      }),
      parent: document.querySelector("#" + $(div).attr('id'))
    });
  }
});