<!DOCTYPE html>
<html>
    <head>
        <link href="https://fonts.googleapis.com/css?family=Verdana&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css?family=Cabin&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css?family=Times+New+Roman&display=swap" rel="stylesheet" />
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" />
        <link href="{{ asset('css/print.css') }}" rel="stylesheet" />
        <title>Document</title>
        </head>
        <body>
            <div class="v12_146">
                <div class="v12_149"></div>
                <div class="v12_150">
                    <div class="v12_151"></div>
                    <span class="v12_319">Msk Area U</span>
                    <div class="v12_152"></div>
                    <div class="v12_153"></div>
                    <div class="v12_154"></div>
                    <div class="v12_155"></div>
                    <div class="v12_156"></div>
                    <span class="v12_157">{{$ticket->customer->name}} </span>
                    <div class="v12_158"></div>
                    <div class="v12_159"></div>
                    <div class="v12_160"></div>
                    <div class="v12_161"></div>
                    <div class="v12_162"></div>
                    <div class="v12_163"></div>
                    <div class="v12_164"></div>
                    <div class="v12_165"></div>
                    <div class="v12_166"></div>
                    <div class="v12_167">
                        <div class="v12_168"></div>
                        <div class="v12_169"></div>
                        <div class="v12_170"></div>
                        <div class="v12_171"></div>
                        <div class="v12_172"></div>
                        <div class="v12_173"></div>
                        <div class="v12_174"> </div>
                        <div class="v12_175"></div>
                        <div class="v12_176"></div>
                    </div>
                    <div class="v12_177">
                        <span class="v12_178">1</span>
                        <span class="v12_179">2</span>
                        <span class="v12_180">3</span>
                        <span class="v12_181">4</span>
                        <span class="v12_182">5</span>
                        <span class="v12_183">6</span>
                        <span class="v12_184">7</span>
                        <span class="v12_185">8</span>
                        <span class="v12_186">9</span>
                    </div>
                    <div class="v12_187"></div>
                    <span class="v12_188">Area Unit</span>
                    <span class="v12_189">Pelapor</span>
                    <span class="v12_190">Penerima</span>
                    <span class="v12_191">Melalui</span>
                    <span class="v12_192">NAMA</span>
                    <span class="v12_193">ALAMAT</span>
                    <span class="v12_194">NO.SAMB</span>
                    <span class="v12_195">:</span>
                    <span class="v12_196">:</span>
                    <span class="v12_197">:</span>
                    <div class="v12_198">
                        <div class="v12_199">
                            <span class="v12_200">UNIT WATER METER</span>
                            <span class="v12_201">PIPA PERSIL</span>
                            <span class="v12_202">PIPA DINAS</span>
                            <span class="v12_203">PIPA DISTRIBUSI</span>
                            <span class="v12_204">PIPA TRANSMISI</span>
                            <span class="v12_205">KWAL/KWAN AIR</span>
                            <span class="v12_206">REKENING</span>
                            <span class="v12_207">SAMBUNGAN BARU</span>
                            <span class="v12_208">LAIN-LAIN</span>
                        </div>
                    </div>
                    <span class="v12_209">Nama</span>
                    <span class="v12_210">Paraf</span>
                    <span class="v12_211">Tanggal</span>
                    <span class="v12_212">Jam</span>
                    <span class="v12_213">Keterangan :</span>
                    <span class="v12_214">{{Auth::user()->name}}</span>
                    <span class="v12_70">Telepon</span>
                    <span class="v12_216">Tgl Pelap</span>
                    <span class="v12_217">Jam P</span>
                    <span class="v12_218">Jam Pe</span>
                    <span class="v12_219">Prf P</span>
                    <span class="v12_220">Prf Pe</span>
                    <span class="v12_76">Langsung</span>
                    <span class="v12_222">Tgl Pener</span>
                    <span class="v12_73">Pesan</span>
                    <span class="v12_224">PERMINTAAN SERVICE</span>
                    <div class="v12_225"></div>
                    <span class="v12_227">{{$ticket->customer->name}}</span>
                    <span class="v12_228">{{$ticket->customer->address}}</span>
                    <span class="v12_229">Masukan No.Samb</span>
                    <div class="v12_230"></div>
                    <span class="v12_231">{{$ticket->description}}</span>
                    <span class="v20_175">{{$ticket->customer->phone}}</span>
                    {{-- <div class="v12_320" style="background-image: url('{{ asset('images/print.png') }}')"></div> --}}
                    <div class="v12_320" style="background-image: url('{{ asset('images/print.png') }}')"></div>
                </div>
                <div class="v12_232">
                    <div class="v12_233"></div>
                    <span class="v12_234">Masukan NO SPK</span>
                    <div class="v12_235">
                </div>
                    <span class="v12_236">Masukan Tgl</span>
                    <span class="v12_237">NO</span>
                    <span class="v12_238">.</span>
                    <span class="v12_239">SPK</span>
                    <span class="v12_240">Tanggal</span>
                    <div class="v12_241"></div>
                    <div class="v12_242"></div>
                    <span class="v12_243">Tindakan yang dilakukan :</span>
                    <span class="v12_244">Dikerjakan oleh  :</span>
                    <span class="v12_245">Diperiksaoleh     :</span>
                    <span class="v12_246">Selesai</span>
                    <span class="v12_247">Kode Service</span>
                    <span class="v12_248">Keluhan</span>
                    <span class="v12_249">Waktu</span>
                    <span class="v12_250">PERSH.</span>
                    <span class="v12_251">PELANGGAN</span>
                    <span class="v12_252">Biaya ditanggung oleh</span>
                    <span class="v12_253">Diterima baik</span>
                    <div class="v12_254"></div>
                    <div class="v12_255"></div>
                    <div class="v12_256"></div>
                    <div class="v12_257"></div>
                    <div class="v12_258"></div>
                    <div class="v12_259"></div>
                    <div class="v12_260"></div>
                    <div class="v12_261"></div>
                    <div class="v12_262"></div>
                    <div class="v12_263"></div>
                    <div class="v12_264"></div>
                    <div class="v12_265"></div>
                    <div class="v12_266"></div>
                    <div class="v12_267"></div>
                    <div class="v12_268"></div>
                    <div class="v12_269"></div>
                    <span class="v12_305">Tanggal</span>
                    <div class="v12_270"></div>
                    <div class="v12_271"></div>
                    <div class="v12_272"></div>
                    <span class="v12_310">Msk Keluh</span>
                    <div class="v12_273"></div>
                    <div class="v12_274"></div>
                    <div class="v12_275"></div>
                    <span class="v12_292">Nama</span>
                    <span class="v12_293">Paraf</span>
                    <span class="v12_294">Tanggal</span>
                    <span class="v12_295">Jam</span>
                    <span class="v12_296">Masukan Dikerjakan</span>
                    <span class="v12_297">Masukan Diperiksa</span>
                    <span class="v12_298">Prf 2</span>
                    <span class="v12_322">Prf 1</span>
                    <span class="v12_301">Tgl 2</span>
                    <span class="v12_323">Tgl 1</span>
                    <span class="v12_302">Jam 2</span>
                    <span class="v12_303">Jam 1</span>
                    <span class="v12_304">Masukan Tindakan</span>
                    <span class="v12_308">Jam</span>
                    <span class="v12_309">Jumlah Biaya Rp</span>
                    <span class="v12_312">Msk Wkt</span>
                    <span class="v12_313">pr..</span>
                    <span class="v12_314">Plggn</span>
                    <span class="v12_315">Tgl Sel</span>
                    <span class="v12_316">JamS</span>
                    <span class="v12_317">Jml Bia</span>
                </div>
                <div class="v12_276">
                    <span class="v12_277">PERUSAHAAN UMUM DAERAH AIR MINUM TIRTA AMERTHA BUANA</span>
                    <span class="v12_278">PERMINTAAN SERVICE</span><span class="v12_291">FORM-A</span>
                </div>
                <div class="v12_279"></div>
                <div class="v12_280">
                    <div class="v12_281"></div>
                    <span class="v12_282">MASYARAKAT / PELANGGAN</span>
                </div>
                <div class="v12_283">
                    <div class="v12_284"></div>
                    <span class="v12_285">PERUSAHAAN UMUM DAERAH AIR MINUM TIRTA AMERTHA BUANA</span>
                </div>
                <div class="v12_286">
                    <span class="v12_287">Catatan : LINGKARI / SILANG pilihan yang sesuai</span>
                    <span class="v12_288">TEMBUSAN :</span>
                    <span class="v12_289">1.Bagian Keuangan 2.Bagian Gudang 3.Bagian Peminta Barang 4.Bagian Langganan</span>
                    <div class="v12_325"></div>
                </div>
                {{-- <div class="vCustom">
                   <div  class="buttonPrint">
                       <a href="{{route('admin.tickets.printAction', $ticket->id)}}" class="btn btn-sm btn-primary" >Print</a>
                   </div>
                </div> --}}
            </div>
        </body>
</html>