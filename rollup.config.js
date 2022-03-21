import {nodeResolve} from "@rollup/plugin-node-resolve"
export default {
  input: "./codemirror.js",
  output: {
    file: "./Assets/JS/codemirrorBundle.js",
    format: "iife"
  },
  plugins: [nodeResolve()]
}