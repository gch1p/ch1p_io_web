#!/usr/bin/env node
const {generateCSSPatch} = require('css-patch')
const fs = require('fs')

const files = process.argv.slice(2)
if (files.length !== 2) {
    console.log(`usage: ${process.argv[0]} file1 file2`)
    process.exit()
}

const css1 = fs.readFileSync(files[0], 'utf-8')
const css2 = fs.readFileSync(files[1], 'utf-8')

console.log(generateCSSPatch(css1, css2))