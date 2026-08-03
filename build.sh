echo "Compiling necessary stuff..."
cd ./tainacan-mapster
npm install
npm run build
cd ../

if [ -z "$1" ]
then
    echo "Done!"
else
    echo "Done. Moving files to destination folder: $1"
    rm -rf $1/tainacan-mapster
    cp -r ./tainacan-mapster $1
    echo "Cleaning some files not necessary for the plugin to work..."
    rm -f $1/tainacan-mapster/package.json
    rm -f $1/tainacan-mapster/package-lock.json
    rm -rf $1/tainacan-mapster/node_modules
    # Vue SFC / build tooling: human-readable source lives on GitHub (readme Development).
    rm -f $1/tainacan-mapster/metadata_type/build.js
    rm -f $1/tainacan-mapster/metadata_type/metadata-type.js
    rm -f $1/tainacan-mapster/metadata_type/metadata-type.vue
    if [ -f ./LICENSE ]; then
        cp ./LICENSE $1/tainacan-mapster/LICENSE
    fi
    echo "Done!"
fi
