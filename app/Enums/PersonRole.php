<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static PersonRole Other()
 * @method static PersonRole AnimationCharacterDesign()
 * @method static PersonRole AnimationDirector()
 * @method static PersonRole AnimationDirectorAssistant()
 * @method static PersonRole AnimationProducer()
 * @method static PersonRole AnimationProduction()
 * @method static PersonRole AnimationProductionAssistance()
 * @method static PersonRole AnimationSupervision()
 * @method static PersonRole Animator()
 * @method static PersonRole Art()
 * @method static PersonRole ArtAssistant()
 * @method static PersonRole ArtDirector()
 * @method static PersonRole ArtDirectorAssistant()
 * @method static PersonRole ArtSetting()
 * @method static PersonRole Assistance()
 * @method static PersonRole AssistantDirector()
 * @method static PersonRole AssistantEpisodeDirector()
 * @method static PersonRole AssistantProducer()
 * @method static PersonRole AssociateProducer()
 * @method static PersonRole AudioRecording()
 * @method static PersonRole AudioRecordingAdjustment()
 * @method static PersonRole AudioRecordingAssistant()
 * @method static PersonRole AudioRecordingDirection()
 * @method static PersonRole AudioRecordingProduction()
 * @method static PersonRole AudioRecordingStudio()
 * @method static PersonRole BackgroundArt()
 * @method static PersonRole BackgroundArtProcessing()
 * @method static PersonRole CGIDirector()
 * @method static PersonRole CGIProducer()
 * @method static PersonRole CGIProduction()
 * @method static PersonRole CGIProductionDesk()
 * @method static PersonRole CGIProductionManagerAssistant()
 * @method static PersonRole CellInspection()
 * @method static PersonRole CharacterDesign()
 * @method static PersonRole ChiefAnimationDirector()
 * @method static PersonRole ChiefAnimationSupervisor()
 * @method static PersonRole ChiefDirector()
 * @method static PersonRole ChiefProducer()
 * @method static PersonRole ChiefProductionAdvancement()
 * @method static PersonRole ChiefSupervision()
 * @method static PersonRole ColorDesign()
 * @method static PersonRole ColourDesign()
 * @method static PersonRole ColourDesignAssistance()
 * @method static PersonRole ColourSpecificationInspection()
 * @method static PersonRole Composer()
 * @method static PersonRole ConceptDesign()
 * @method static PersonRole CreativeProducer()
 * @method static PersonRole Creator()
 * @method static PersonRole CreatureDesign()
 * @method static PersonRole DVDProducer()
 * @method static PersonRole Design()
 * @method static PersonRole DesignManager()
 * @method static PersonRole DesignProduction()
 * @method static PersonRole DigitalColouring()
 * @method static PersonRole DigitalDirector()
 * @method static PersonRole DigitalPhotography()
 * @method static PersonRole DigitalProduction()
 * @method static PersonRole Director()
 * @method static PersonRole DirectorOfDigitalPhotography()
 * @method static PersonRole DirectorOfPhotography()
 * @method static PersonRole Editor()
 * @method static PersonRole EditorAssistant()
 * @method static PersonRole EpisodeDirector()
 * @method static PersonRole ExecutiveProducer()
 * @method static PersonRole Film()
 * @method static PersonRole FilmEditing()
 * @method static PersonRole FilmProcessing()
 * @method static PersonRole FinancialProduction()
 * @method static PersonRole GeneralManager()
 * @method static PersonRole HDEditor()
 * @method static PersonRole ImageBoard()
 * @method static PersonRole InBetweenAnimation()
 * @method static PersonRole InBetweenAnimationAssistance()
 * @method static PersonRole InBetweenAnimationCheck()
 * @method static PersonRole InBetweenAnimationInspection()
 * @method static PersonRole KeyAnimation()
 * @method static PersonRole Layout()
 * @method static PersonRole LogoDesign()
 * @method static PersonRole Lyrics()
 * @method static PersonRole MainAnimator()
 * @method static PersonRole MainTitleDesign()
 * @method static PersonRole MainTitlePhotography()
 * @method static PersonRole MechanicalDesign()
 * @method static PersonRole Music()
 * @method static PersonRole MusicArrangement()
 * @method static PersonRole MusicAssistance()
 * @method static PersonRole MusicDirector()
 * @method static PersonRole MusicEngineer()
 * @method static PersonRole MusicManager()
 * @method static PersonRole MusicProducer()
 * @method static PersonRole MusicProduction()
 * @method static PersonRole MusicProductionAssistance()
 * @method static PersonRole OnlineEditor()
 * @method static PersonRole OnlineEditorManager()
 * @method static PersonRole OpeningAnimation()
 * @method static PersonRole OriginalCharacterDesign()
 * @method static PersonRole OriginalCreator()
 * @method static PersonRole OriginalIllustration()
 * @method static PersonRole OriginalStory()
 * @method static PersonRole Photography()
 * @method static PersonRole PhotographyAssistance()
 * @method static PersonRole Planning()
 * @method static PersonRole PlanningCooperation()
 * @method static PersonRole PlanningManager()
 * @method static PersonRole Producer()
 * @method static PersonRole Production()
 * @method static PersonRole ProductionAssistance()
 * @method static PersonRole ProductionAssistant()
 * @method static PersonRole ProductionControl()
 * @method static PersonRole ProductionDesk()
 * @method static PersonRole ProductionManager()
 * @method static PersonRole ProductionOfficeWork()
 * @method static PersonRole ProductionStudio()
 * @method static PersonRole Promotion()
 * @method static PersonRole PropDesign()
 * @method static PersonRole Publication()
 * @method static PersonRole Publicity()
 * @method static PersonRole PublicityAssistance()
 * @method static PersonRole Screenplay()
 * @method static PersonRole SecondKeyAnimator()
 * @method static PersonRole SellingAgency()
 * @method static PersonRole SeriesComposition()
 * @method static PersonRole SeriesCompositionAssistant()
 * @method static PersonRole SeriesDirector()
 * @method static PersonRole SeriesEpisodeDirector()
 * @method static PersonRole SetDesign()
 * @method static PersonRole SettingProduction()
 * @method static PersonRole Sound()
 * @method static PersonRole SoundDirector()
 * @method static PersonRole SoundEffects()
 * @method static PersonRole SoundMixer()
 * @method static PersonRole SoundProduction()
 * @method static PersonRole SoundWorkManager()
 * @method static PersonRole SpecialEffects()
 * @method static PersonRole Sponsor()
 * @method static PersonRole StoryComposition()
 * @method static PersonRole Storyboard()
 * @method static PersonRole Supervision()
 * @method static PersonRole Titling()
 * @method static PersonRole TouchUp()
 * @method static PersonRole TouchUpAssistance()
 * @method static PersonRole TouchUpInspection()
 * @method static PersonRole TouchUpManager()
 * @method static PersonRole TwoDimensionalEffects()
 * @method static PersonRole TwoDimensionalEffectsChief()
 * @method static PersonRole Vocal()
 * @method static PersonRole VoiceActor()
 */
final class PersonRole extends Enum
{
    const Other = 0;
    const AnimationCharacterDesign = 1;
    const AnimationDirector = 2;
    const AnimationDirectorAssistant = 3;
    const AnimationProducer = 4;
    const AnimationProduction = 5;
    const AnimationProductionAssistance = 6;
    const AnimationSupervision = 7;
    const Animator = 8;
    const Art = 9;
    const ArtAssistant = 10;
    const ArtDirector = 11;
    const ArtDirectorAssistant = 12;
    const ArtSetting = 13;
    const Assistance = 14;
    const AssistantDirector = 15;
    const AssistantEpisodeDirector = 16;
    const AssistantProducer = 17;
    const AssociateProducer = 18;
    const AudioRecording = 19;
    const AudioRecordingAdjustment = 20;
    const AudioRecordingAssistant = 21;
    const AudioRecordingDirection = 22;
    const AudioRecordingProduction = 23;
    const AudioRecordingStudio = 24;
    const BackgroundArt = 25;
    const BackgroundArtProcessing = 26;
    const CGIDirector = 27;
    const CGIProducer = 28;
    const CGIProduction = 29;
    const CGIProductionDesk = 30;
    const CGIProductionManagerAssistant = 31;
    const CellInspection = 32;
    const CharacterDesign = 33;
    const ChiefAnimationDirector = 34;
    const ChiefAnimationSupervisor = 35;
    const ChiefDirector = 36;
    const ChiefProducer = 37;
    const ChiefProductionAdvancement = 38;
    const ChiefSupervision = 39;
    const ColorDesign = 40;
    const ColourDesign = 41;
    const ColourDesignAssistance = 42;
    const ColourSpecificationInspection = 43;
    const Composer = 44;
    const ConceptDesign = 45;
    const CreativeProducer = 46;
    const Creator = 47;
    const CreatureDesign = 48;
    const DVDProducer = 49;
    const Design = 50;
    const DesignManager = 51;
    const DesignProduction = 52;
    const DigitalColouring = 53;
    const DigitalDirector = 54;
    const DigitalPhotography = 55;
    const DigitalProduction = 56;
    const Director = 57;
    const DirectorOfDigitalPhotography = 58;
    const DirectorOfPhotography = 59;
    const Editor = 60;
    const EditorAssistant = 61;
    const EpisodeDirector = 62;
    const ExecutiveProducer = 63;
    const Film = 64;
    const FilmEditing = 65;
    const FilmProcessing = 66;
    const FinancialProduction = 67;
    const GeneralManager = 68;
    const HDEditor = 69;
    const ImageBoard = 70;
    const InBetweenAnimation = 71;
    const InBetweenAnimationAssistance = 72;
    const InBetweenAnimationCheck = 73;
    const InBetweenAnimationInspection = 74;
    const KeyAnimation = 75;
    const Layout = 76;
    const LogoDesign = 77;
    const Lyrics = 78;
    const MainAnimator = 79;
    const MainTitleDesign = 80;
    const MainTitlePhotography = 81;
    const MechanicalDesign = 82;
    const Music = 83;
    const MusicArrangement = 84;
    const MusicAssistance = 85;
    const MusicDirector = 86;
    const MusicEngineer = 87;
    const MusicManager = 88;
    const MusicProducer = 89;
    const MusicProduction = 90;
    const MusicProductionAssistance = 91;
    const OnlineEditor = 92;
    const OnlineEditorManager = 93;
    const OpeningAnimation = 94;
    const OriginalCharacterDesign = 95;
    const OriginalCreator = 96;
    const OriginalIllustration = 97;
    const OriginalStory = 98;
    const Photography = 99;
    const PhotographyAssistance = 100;
    const Planning = 101;
    const PlanningCooperation = 102;
    const PlanningManager = 103;
    const Producer = 104;
    const Production = 105;
    const ProductionAssistance = 106;
    const ProductionAssistant = 107;
    const ProductionControl = 108;
    const ProductionDesk = 109;
    const ProductionManager = 110;
    const ProductionOfficeWork = 111;
    const ProductionStudio = 112;
    const Promotion = 113;
    const PropDesign = 114;
    const Publication = 115;
    const Publicity = 116;
    const PublicityAssistance = 117;
    const Screenplay = 118;
    const SecondKeyAnimator = 119;
    const SellingAgency = 120;
    const SeriesComposition = 121;
    const SeriesCompositionAssistant = 122;
    const SeriesDirector = 123;
    const SeriesEpisodeDirector = 124;
    const SetDesign = 125;
    const SettingProduction = 126;
    const Sound = 127;
    const SoundDirector = 128;
    const SoundEffects = 129;
    const SoundMixer = 130;
    const SoundProduction = 131;
    const SoundWorkManager = 132;
    const SpecialEffects = 133;
    const Sponsor = 134;
    const StoryComposition = 135;
    const Storyboard = 136;
    const Supervision = 137;
    const Titling = 138;
    const TouchUp = 139;
    const TouchUpAssistance = 140;
    const TouchUpInspection = 141;
    const TouchUpManager = 142;
    const TwoDimensionalEffects = 143;
    const TwoDimensionalEffectsChief = 144;
    const Vocal = 145;
    const VoiceActor = 146;
}
