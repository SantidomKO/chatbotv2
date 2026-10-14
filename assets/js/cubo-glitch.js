// Cubo Glitch 3D - Logo para A.U.R.A
// Adaptado para uso como componente de logo sin background ni efectos overlay

(function() {
  "use strict";

  const CONFIG = {
    cubeSize: 0.5,
    detail: 4,
    roundness: 0.17,
    edgeLow: 0.46,
    edgeHigh: 2.0,
    glitchIntensity: 0.09,
    glitchSpeed: 3.2,
    autoRotateSpeed: 0.1,
    tiltStrength: 0.025,
    restingTiltDeg: { pitch: 0, yaw: 0, roll: 0 },
    tiltInvertX: true,
    tiltInvertY: false,
    eyeScale: 1.0,
    eyeColorHSL: { h:0.80, s:0.5, l:0.45 },
    leftEye:  { x:-0.2, y:0.1, w:0.115, h:0.235 },
    rightEye: { x: 0.2, y:0.1, w:0.115, h:0.235 },
    bloom: false // Desactivado para uso como logo
  };

  const FACE_COLORS = [
    new THREE.Color(0x5be8ff),
    new THREE.Color(0x5bffd6),
    new THREE.Color(0x8dffb0),
    new THREE.Color(0x63f2c9),
  ];
  const EDGE_HUE_A = 0.58;
  const EDGE_HUE_B = 0.86;

  function initGlitchLogo(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const canvas = document.createElement('canvas');
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.style.display = 'block';
    container.appendChild(canvas);

    const renderer = new THREE.WebGLRenderer({ canvas, antialias:true, alpha:true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio||1, 2));
    renderer.setClearColor(0x000000, 0); // Transparente

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(32, 1, 0.1, 100);
    camera.position.set(0, 0, 2.85);
    camera.lookAt(0,0,0);

    const camForward = camera.position.clone().normalize();
    const worldUp = new THREE.Vector3(0,1,0);
    const camRight = new THREE.Vector3().crossVectors(worldUp, camForward).normalize();
    const camUp = new THREE.Vector3().crossVectors(camForward, camRight).normalize();

    const cyanLight = new THREE.PointLight(0x39e8ff, 2.0, 12);
    cyanLight.position.set(2, 1.4, 3);
    scene.add(cyanLight);
    const magentaLight = new THREE.PointLight(0xb936ff, 1.8, 12);
    magentaLight.position.set(-2, -1.2, 3);
    scene.add(magentaLight);
    const rim = new THREE.DirectionalLight(0xffffff, 0.8);
    rim.position.set(0, 3, -2);
    scene.add(rim);

    function cubify(x,y,z){
      const m = Math.max(Math.abs(x), Math.abs(y), Math.abs(z)) || 1;
      return [x/m, y/m, z/m];
    }

    const ico = new THREE.IcosahedronGeometry(1, CONFIG.detail);
    ico.deleteAttribute('normal');
    ico.deleteAttribute('uv');
    const geo = ico.toNonIndexed();
    const posAttr = geo.getAttribute('position');
    const vertCount = posAttr.count;

    const restPositions = new Float32Array(vertCount*3);
    const outwardDir   = new Float32Array(vertCount*3);
    const edgeFactor   = new Float32Array(vertCount);
    const faceAxis     = new Uint8Array(vertCount);
    const faceSign     = new Int8Array(vertCount);
    const seed         = new Float32Array(vertCount);
    const colorAttr    = new Float32Array(vertCount*3);

    function smoothstep(a,b,x){
      const t = Math.min(1, Math.max(0, (x-a)/(b-a)));
      return t*t*(3-2*t);
    }
    function hslToRgb(h,s,l){
      const c = new THREE.Color();
      c.setHSL(h,s,l);
      return c;
    }

    for(let i=0;i<vertCount;i++){
      const ix=i*3;
      let x = posAttr.getX(i), y = posAttr.getY(i), z = posAttr.getZ(i);
      const len = Math.sqrt(x*x+y*y+z*z) || 1;
      x/=len; y/=len; z/=len;

      const [cx,cy,cz] = cubify(x,y,z);

      const fx = cx*(1-CONFIG.roundness) + x*CONFIG.roundness;
      const fy = cy*(1-CONFIG.roundness) + y*CONFIG.roundness;
      const fz = cz*(1-CONFIG.roundness) + z*CONFIG.roundness;

      restPositions[ix]   = fx*CONFIG.cubeSize;
      restPositions[ix+1] = fy*CONFIG.cubeSize;
      restPositions[ix+2] = fz*CONFIG.cubeSize;

      const flen = Math.sqrt(fx*fx+fy*fy+fz*fz)||1;
      outwardDir[ix]=fx/flen; outwardDir[ix+1]=fy/flen; outwardDir[ix+2]=fz/flen;

      const a=[Math.abs(cx),Math.abs(cy),Math.abs(cz)].sort((p,q)=>q-p);
      const second = a[1];
      const ef = smoothstep(CONFIG.edgeLow, CONFIG.edgeHigh, second);
      edgeFactor[i]=ef;

      let axis=0, sign=1, best=Math.abs(cx);
      if(Math.abs(cy)>best){axis=1;best=Math.abs(cy);}
      if(Math.abs(cz)>best){axis=2;best=Math.abs(cz);}
      sign = axis===0?Math.sign(cx||1):axis===1?Math.sign(cy||1):Math.sign(cz||1);
      faceAxis[i]=axis; faceSign[i]=sign;

      seed[i]=Math.random()*1000;

      const palette = FACE_COLORS[(axis*2 + (sign>0?0:1)) % FACE_COLORS.length];
      const centerGlow = 1 - smoothstep(0, 0.72, second);
      const base = palette.clone().lerp(new THREE.Color(0xffffff), centerGlow*0.55);

      colorAttr[ix]=base.r; colorAttr[ix+1]=base.g; colorAttr[ix+2]=base.b;
    }

    geo.setAttribute('position', new THREE.BufferAttribute(restPositions.slice(), 3));
    geo.setAttribute('color', new THREE.BufferAttribute(colorAttr, 3));
    geo.computeVertexNormals();

    const material = new THREE.MeshLambertMaterial({
      vertexColors: true,
      transparent: true,
      opacity: 1.0,
      emissive: new THREE.Color(0x0c3d3a),
      emissiveIntensity: 0.4,
      side: THREE.DoubleSide
    });

    const cubeMesh = new THREE.Mesh(geo, material);
    const group = new THREE.Group();
    group.add(cubeMesh);

    const glowGeo = geo.clone();
    const glowMat = new THREE.MeshBasicMaterial({
      vertexColors:true, transparent:true, opacity:0.5,
      side: THREE.BackSide, blending: THREE.AdditiveBlending, depthWrite:false
    });
    const glowMesh = new THREE.Mesh(glowGeo, glowMat);
    glowMesh.scale.setScalar(1.045);
    group.add(glowMesh);

    scene.add(group);

    function buildEyeShapeCanvas(){
      const size = 128;
      const c = document.createElement('canvas');
      c.width = c.height = size;
      const ctx = c.getContext('2d');
      ctx.clearRect(0,0,size,size);
      ctx.save();
      ctx.translate(size/2, size/2);
      ctx.beginPath();
      ctx.moveTo(0, -size*0.46);
      ctx.bezierCurveTo(size*0.30, -size*0.44, size*0.33, size*0.28, 0, size*0.46);
      ctx.bezierCurveTo(-size*0.33, size*0.28, -size*0.30, -size*0.44, 0, -size*0.46);
      ctx.closePath();
      ctx.fillStyle = '#ffffff';
      ctx.shadowColor = 'rgba(255,255,255,0.9)';
      ctx.shadowBlur = 5;
      ctx.fill();
      ctx.restore();
      return c;
    }
    const eyeColor = hslToRgb(CONFIG.eyeColorHSL.h, CONFIG.eyeColorHSL.s, CONFIG.eyeColorHSL.l);

    function buildEye(cfg){
      const g = new THREE.Group();
      const size = 128;
      const cleanCanvas = buildEyeShapeCanvas();
      const workCanvas = document.createElement('canvas');
      workCanvas.width = workCanvas.height = size;
      const workCtx = workCanvas.getContext('2d');
      workCtx.drawImage(cleanCanvas, 0, 0);
      const texture = new THREE.CanvasTexture(workCanvas);

      const planeGeo = new THREE.PlaneGeometry(1,1);
      const mat = new THREE.MeshBasicMaterial({
        map: texture, color: eyeColor,
        transparent: true, depthWrite:false, side: THREE.DoubleSide
      });
      const mesh = new THREE.Mesh(planeGeo, mat);
      mesh.scale.set(cfg.w*CONFIG.eyeScale*2, cfg.h*CONFIG.eyeScale, 1);
      g.add(mesh);

      g.position.set(cfg.x, cfg.y, CONFIG.cubeSize*1.12);
      mesh.renderOrder = 10;
      g.userData = {
        mesh, baseScale: mesh.scale.clone(), phase: Math.random()*10,
        cleanCanvas, workCanvas, workCtx, texture, size
      };
      return g;
    }

    const leftEye  = buildEye(CONFIG.leftEye);
    const rightEye = buildEye(CONFIG.rightEye);
    group.add(leftEye, rightEye);

    function updateEyeGlitch(eye, time){
      const ud = eye.userData;
      const ctx = ud.workCtx;
      const w = ud.size, h = ud.size;
      ctx.clearRect(0,0,w,h);

      const sliceCount = 14;
      const sliceH = h / sliceCount;
      for(let s=0; s<sliceCount; s++){
        const y = s*sliceH;
        const baseJitter = Math.sin(time*5 + s*1.4 + ud.phase) * 0.8;
        const burstJitter = (Math.random()-0.5) * 100 * glitchBurst;
        const offsetX = baseJitter + burstJitter;
        ctx.drawImage(ud.cleanCanvas, 0, y, w, sliceH+1, offsetX, y, w, sliceH+1);
      }

      ctx.globalCompositeOperation = 'destination-out';
      ctx.fillStyle = `rgba(0,0,0,${0.30 + glitchBurst*0.35})`;
      const stripeStep = 4;
      for(let y=0; y<h; y+=stripeStep){
        ctx.fillRect(0, y, w, 1.5);
      }
      ctx.globalCompositeOperation = 'source-over';

      if(glitchBurst > 0.45 && Math.random() < 0.35){
        const dy = Math.random()*h;
        ctx.clearRect(0, dy, w, 2 + Math.random()*5);
      }

      ud.texture.needsUpdate = true;
    }

    const lookAtCameraQuat = new THREE.Quaternion()
      .setFromUnitVectors(new THREE.Vector3(0,0,1), camForward);

    function camAxisQuat(axis, deg){
      return new THREE.Quaternion().setFromAxisAngle(axis, THREE.MathUtils.degToRad(deg));
    }
    const restingOffsetQuat = camAxisQuat(camUp, CONFIG.restingTiltDeg.yaw)
      .multiply(camAxisQuat(camRight, CONFIG.restingTiltDeg.pitch))
      .multiply(camAxisQuat(camForward, CONFIG.restingTiltDeg.roll));

    const baseQuat = restingOffsetQuat.clone().multiply(lookAtCameraQuat);
    group.quaternion.copy(baseQuat);

    let pointerNX=0, pointerNY=0, curNX=0, curNY=0;
    function onPointer(clientX, clientY){
      const rect = container.getBoundingClientRect();
      pointerNX = ((clientX-rect.left)/rect.width)*2-1;
      pointerNY = ((clientY-rect.top)/rect.height)*2-1;
    }
    window.addEventListener('mousemove', e=>onPointer(e.clientX,e.clientY));
    window.addEventListener('touchmove', e=>{
      if(e.touches&&e.touches[0]) onPointer(e.touches[0].clientX, e.touches[0].clientY);
    }, {passive:true});

    function triggerClickGlitch(){
      glitchBurst = 1;
      burstClock = 0;
      nextBurstAt = 1.6 + Math.random()*3.0;
    }
    window.addEventListener('mousedown', triggerClickGlitch);
    window.addEventListener('touchstart', triggerClickGlitch, {passive:true});

    function resize(){
      const w = container.clientWidth, h = container.clientHeight;
      const dpr = Math.min(window.devicePixelRatio||1, 2);
      renderer.setSize(w,h,false);
      camera.aspect = w/h;
      camera.updateProjectionMatrix();
    }
    window.addEventListener('resize', resize);
    resize();

    const posArr = geo.getAttribute('position');
    const colArr = geo.getAttribute('color');
    const glowPosArr = glowGeo.getAttribute('position');
    const glowColArr = glowGeo.getAttribute('color');

    let t0 = performance.now();
    let frame = 0;
    let glitchBurst = 0;
    let nextBurstAt = 1.5 + Math.random()*2.5;
    let burstClock = 0;

    function animate(now){
      const dt = Math.min(0.05,(now - t0)/1000);
      t0 = now;
      frame++;
      const time = now*0.001;

      burstClock += dt;
      if(burstClock > nextBurstAt){
        glitchBurst = 1;
        burstClock = 0;
        nextBurstAt = 1.6 + Math.random()*3.0;
      }
      glitchBurst = Math.max(0, glitchBurst - dt*2.2);

      const doHeavyUpdate = (frame % 2 === 0);
      if(doHeavyUpdate){
        for(let i=0;i<vertCount;i++){
          const ix=i*3;
          const ef = edgeFactor[i];
          let px=restPositions[ix], py=restPositions[ix+1], pz=restPositions[ix+2];

          if(ef>0.001){
            const j = Math.sin(time*CONFIG.glitchSpeed + seed[i]) * ef * CONFIG.glitchIntensity
                      * (1 + glitchBurst*2.2);
            px += outwardDir[ix]*j;
            py += outwardDir[ix+1]*j;
            pz += outwardDir[ix+2]*j;
          }
          posArr.setXYZ(i, px,py,pz);
          glowPosArr.setXYZ(i, px,py,pz);

          if(ef>0.02){
            const hueMix = 0.5 + 0.5*Math.sin(time*0.6 + seed[i]*0.3 + faceAxis[i]);
            const hue = EDGE_HUE_A + (EDGE_HUE_B-EDGE_HUE_A)*hueMix;
            const edgeCol = hslToRgb(hue, 0.95, 0.58 + glitchBurst*0.12);
            const baseR=colorAttr[ix], baseG=colorAttr[ix+1], baseB=colorAttr[ix+2];
            const mixT = ef;
            colArr.setXYZ(i,
              baseR*(1-mixT)+edgeCol.r*mixT,
              baseG*(1-mixT)+edgeCol.g*mixT,
              baseB*(1-mixT)+edgeCol.b*mixT
            );
            glowColArr.setXYZ(i, edgeCol.r, edgeCol.g, edgeCol.b);
          } else {
            colArr.setXYZ(i, colorAttr[ix], colorAttr[ix+1], colorAttr[ix+2]);
            glowColArr.setXYZ(i, colorAttr[ix], colorAttr[ix+1], colorAttr[ix+2]);
          }
        }
        posArr.needsUpdate = true;
        colArr.needsUpdate = true;
        glowPosArr.needsUpdate = true;
        glowColArr.needsUpdate = true;
        if(frame % 6 === 0){ geo.computeVertexNormals(); }
      }

      updateEyeGlitch(leftEye, time);
      updateEyeGlitch(rightEye, time);

      curNX += (pointerNX-curNX)*0.06;
      curNY += (pointerNY-curNY)*0.06;

      const signX = CONFIG.tiltInvertX ? -1 : 1;
      const signY = CONFIG.tiltInvertY ? -1 : 1;
      const tiltPitch = -curNY * CONFIG.tiltStrength * 0.7 * signX;
      const tiltYaw   =  curNX * CONFIG.tiltStrength * signY;

      const qTiltPitch = camAxisQuat(camRight, THREE.MathUtils.radToDeg(tiltPitch));
      const qTiltYaw   = camAxisQuat(camUp, THREE.MathUtils.radToDeg(tiltYaw));
      const qIdle      = camAxisQuat(camUp, THREE.MathUtils.radToDeg(Math.sin(time*CONFIG.autoRotateSpeed)*0.06));

      group.quaternion.copy(qTiltYaw.multiply(qTiltPitch).multiply(qIdle).multiply(baseQuat));

      cyanLight.intensity = 1.2 + Math.sin(time*1.3)*0.25 + glitchBurst*0.6;
      magentaLight.intensity = 1.0 + Math.cos(time*1.1)*0.2 + glitchBurst*0.5;

      renderer.render(scene, camera);
      requestAnimationFrame(animate);
    }
    requestAnimationFrame(animate);
  }

  // Inicializar cuando Three.js esté cargado
  if (typeof THREE !== 'undefined') {
    initGlitchLogo('glitch-logo-container');
  } else {
    console.error('Three.js no está cargado. Carga la librería antes de cubo-glitch.js');
  }
})();
