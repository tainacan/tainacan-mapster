/**
 * Build the metadata-type Vue SFC into a Tainacan-compatible script.
 *
 * Tainacan registers extra components into its own Vue app. Bundling a second
 * Vue runtime (vue-loader default) breaks Buefy scoped slots. Instead we ship
 * an Options API object with a runtime `template` string so the host Vue
 * compiles and renders it.
 */
const fs = require('fs');
const path = require('path');
const { parse } = require('@vue/compiler-sfc');

const root = __dirname;
const vuePath = path.join(root, 'metadata-type.vue');
const outDir = path.join(root, 'dist');
const outPath = path.join(outDir, 'metadata-type.bundle.js');
const componentKey = 'tainacan-metadata-type-mapster-single-feature';

const source = fs.readFileSync(vuePath, 'utf8');
const { descriptor, errors } = parse(source, { filename: 'metadata-type.vue' });

if (errors && errors.length) {
	console.error(errors);
	process.exit(1);
}

if (!descriptor.script) {
	console.error('metadata-type.vue must have a <script> block.');
	process.exit(1);
}

if (!descriptor.template) {
	console.error('metadata-type.vue must have a <template> block.');
	process.exit(1);
}

let script = descriptor.script.content.trim();

if (!/^export\s+default\s+/m.test(script)) {
	console.error('metadata-type.vue <script> must use `export default { ... }`.');
	process.exit(1);
}

script = script.replace(/^export\s+default\s+/m, 'var __tainacanMapsterMetadataTypeComponent = ');

const template = descriptor.template.content;
const css = descriptor.styles.map((style) => style.content).join('\n').trim();

const styleInjection = css
	? `
(function () {
	if (document.getElementById('tainacan-mapster-metadata-type-styles')) {
		return;
	}
	var styleEl = document.createElement('style');
	styleEl.id = 'tainacan-mapster-metadata-type-styles';
	styleEl.textContent = ${JSON.stringify(css)};
	document.head.appendChild(styleEl);
})();
`
	: '';

const bundle = `/*! Tainacan Mapster metadata type – built from metadata-type.vue */
(function () {
	if (window.tainacan_extra_components && window.tainacan_extra_components[${JSON.stringify(componentKey)}]) {
		return;
	}

	window.tainacan_extra_components = typeof window.tainacan_extra_components !== 'undefined'
		? window.tainacan_extra_components
		: {};
${styleInjection}
${script}

	__tainacanMapsterMetadataTypeComponent.template = ${JSON.stringify(template)};
	window.tainacan_extra_components[${JSON.stringify(componentKey)}] = __tainacanMapsterMetadataTypeComponent;
})();
`;

fs.mkdirSync(outDir, { recursive: true });
fs.writeFileSync(outPath, bundle, 'utf8');
console.log('Built', path.relative(path.join(root, '..'), outPath), `(${Buffer.byteLength(bundle)} bytes)`);
